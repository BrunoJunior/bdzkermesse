<?php

namespace App\Service;

use App\DataTransfer\ContactDTO;
use App\Entity\Etablissement;
use App\Entity\Inscription;
use App\Entity\Membre;
use App\Enum\InscriptionStatutEnum;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
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
     * Dummy consrtructor
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param PasswordGenerator $pwdGenerator
     * @param MailgunSender $sender
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     */
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        PasswordGenerator $pwdGenerator,
        MailgunSender $sender,
        EntityManagerInterface $em,
        RouterInterface $router
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->sender = $sender;
        $this->pwdGenerator = $pwdGenerator;
        $this->em = $em;
        $this->router = $router;
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
     * @param Inscription $inscription
     * @param string $titre
     * @return ContactDTO
     */
    private function genererContact(Inscription $inscription, string $titre): ContactDTO {
        return (new ContactDTO())
            ->setEmetteur("no-reply@web-project.fr")
            ->setTitre($titre)
            ->setDestinataire($inscription->getContactEmail());
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
            ->envoyer($this->genererContact($inscription, 'Ouverture de compte validée'));
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
            $this->genererContact($inscription, 'Ouverture de compte refusée')
        );
    }
}
