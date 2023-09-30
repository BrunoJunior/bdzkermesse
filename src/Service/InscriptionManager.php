<?php

namespace App\Service;

use App\DataTransfer\ContactDTO;
use App\Entity\Etablissement;
use App\Entity\Inscription;
use App\Entity\Membre;
use App\Enum\InscriptionStatutEnum;
use App\Form\ForgotPasswordType;
use App\Form\ForgotUsernameType;
use App\Repository\EtablissementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Gestion des inscriptions
 * @package App\Service
 * @author bruno <bdesprez@thalassa.fr>
 */
class InscriptionManager {

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var PasswordGenerator
     */
    private $pwdGenerator;

    /**
     * @var MailgunSender
     */
    private $sender;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EtablissementRepository
     */
    private $rEtab;

    /**
     * Dummy consrtructor
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param PasswordGenerator $pwdGenerator
     * @param MailgunSender $sender
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     * @param FormFactoryInterface $formFactory
     * @param EtablissementRepository $rEtab
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        PasswordGenerator $pwdGenerator,
        MailgunSender $sender,
        EntityManagerInterface $em,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        EtablissementRepository $rEtab
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->sender = $sender;
        $this->pwdGenerator = $pwdGenerator;
        $this->em = $em;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->rEtab = $rEtab;
    }

    /**
     * Génération du membre à partir des infos de l'établissement
     * @param Etablissement $etablissement
     * @return void
     */
    private function genererMembre(Etablissement $etablissement): void {
        $partiesNom = explode(' ', $etablissement->getNom());
        $membre = new Membre();
        $membre->setDefaut(false);
        $membre->setGestionnaire(true);
        $membre->setEmail($etablissement->getEmail());
        $membre->setPrenom(array_shift($partiesNom));
        $membre->setNom(implode(' ', $partiesNom));
        // On enregistre l'utilisateur dans la base
        $this->em->persist($membre);
        $etablissement->addMembre($membre);
    }

    /**
     * Création de l'établissement et du membre associé
     * @param Inscription $inscription
     * @return Etablissement
     * @throws Exception
     */
    private function creerCompte(Inscription $inscription): Etablissement {
        $etablissement = new Etablissement();
        $etablissement->setEmail($inscription->getContactEmail());
        $etablissement->setAdmin(false);
        $etablissement->setNom($inscription->getContactName());
        $etablissement->setOriginInscription($inscription);
        $username = mb_strtolower(mb_substr(implode(
            '_', array_filter(
                array_merge(
                    explode(' ', $inscription->getEtablissementNom()),
                    explode(' ', $inscription->getEtablissementVille())
                )
            )
        ), 0, 32));
        $etablissement->setUsername($username);
        $etablissement->setPassword($this->passwordEncoder->encodePassword($etablissement, $this->pwdGenerator->generateSecuredPassword(10)));
        $etablissement->setResetPwdKey($this->pwdGenerator->generateRandomKey());
        $this->genererMembre($etablissement);
        $this->em->persist($etablissement);
        return $etablissement;
    }

    /**
     * Changement de statut de la demande d'inscription : Un mail est envoyé, on attend la validation de l'adresse e-mail
     * @param Inscription $inscription
     * @return void
     */
    private function inscriptionAValider(Inscription $inscription) {
        $inscription->setState(InscriptionStatutEnum::A_VALIDER);
        $this->em->persist($inscription);
    }

    /**
     * Génération du contact d'envoi d'e-mail
     * @param string $email
     * @param string $titre
     * @return ContactDTO
     */
    private function genererContact(string $email, string $titre): ContactDTO {
        return (new ContactDTO())
            ->setEmetteur("no-reply@web-project.fr")
            ->setTitre($titre)
            ->setDestinataire($email);
    }

    /**
     * Valide l'inscription, envoi un email de définition du mot de passe et retourne le nom d'utilisateur généré
     * @param Inscription $inscription
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function valider(Inscription $inscription): string {
        if (!$inscription->getContactEmail() || !filter_var($inscription->getContactEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("E-mail valide obligatoire !");
        }
        $etablissement = $this->creerCompte($inscription);
        $this->inscriptionAValider($inscription);
        $this->em->flush();
        $validationLink = $this->router->generate(
            'validation_email',
            ['id' => $inscription->getId(), 'key' => $etablissement->getResetPwdKey()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $this->sender->setTemplate('inscription_validee')
            ->setTemplateVars(['link' => $validationLink, 'username' => $etablissement->getUsername()])
            ->envoyer($this->genererContact($inscription->getContactEmail(), 'Ouverture de compte validée'));
        return $etablissement->getUsername();
    }

    /**
     * Refuse l'inscription et envoi un e-mail de refus
     * @param Inscription $inscription
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function refuser(Inscription $inscription) {
        $inscription->setState(InscriptionStatutEnum::REFUSEE);
        $this->em->persist($inscription);
        $this->em->flush();
        $this->sender->setTemplate('inscription_refusee')->envoyer(
            $this->genererContact($inscription->getContactEmail(), 'Ouverture de compte refusée')
        );
    }

    /**
     * Envoyer un e-mail pour la réinitialisation du mot de passe
     * @param Request $request
     * @return FormInterface|null
     * @throws Exception
     */
    public function sendForgotPasswordMail(Request $request): ?FormInterface {
        $form = $this->formFactory->create(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Honeypot
            $isSpam = $request->get('name') || $request->get('phone');
            // Si c'est du spam, on fait croire que c'est OK, mais on ne fait rien
            if (!$isSpam) {
                $etab = $this->rEtab->findOneBy(['username' => $form->get('username')->getData()]);
                if (null !== $etab && null !== $etab->getEmail()) {
                    $etab->setResetPwdKey($this->pwdGenerator->generateRandomKey());
                    $this->em->persist($etab);
                    $this->em->flush();
                    $validationLink = $this->router->generate(
                        'reset_pwd',
                        ['id' => $etab->getId(), 'key' => $etab->getResetPwdKey()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    );
                    $this->sender->setTemplate('reinit_password')
                        ->setTemplateVars(['link' => $validationLink])
                        ->envoyer($this->genererContact($etab->getEmail(), 'Réinitialisation de mot de passe'));
                }

            }
            return null;
        }
        return $form;
    }

    /**
     * @param Request $request
     * @return FormInterface|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function sendForgotPasswordIdentifiant(Request $request): ?FormInterface {
        $form = $this->formFactory->create(ForgotUsernameType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Honeypot
            $isSpam = $request->get('name') || $request->get('phone');
            // Si c'est du spam, on fait croire que c'est OK, mais on ne fait rien
            if (!$isSpam) {
                $email = $form->get('email')->getData();
                $etablissements = $this->rEtab->findBy(['email' => $email]);
                if (count($etablissements) > 0) {
                    $this->sender->setTemplate('forgot_ident')
                        ->setTemplateVars(['etablissements' => $etablissements])
                        ->envoyer($this->genererContact($email, 'Identifiants de connexion'));
                }
            }
            return null;
        }
        return $form;
    }
}
