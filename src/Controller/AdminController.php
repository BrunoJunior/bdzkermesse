<?php

namespace App\Controller;

use App\DataTransfer\ContactDTO;
use App\DataTransfer\InscriptionRow;
use App\Entity\Etablissement;
use App\Entity\Inscription;
use App\Entity\Membre;
use App\Enum\InscriptionStatutEnum;
use App\Form\EtablissementType;
use App\Repository\InscriptionRepository;
use App\Service\MailgunSender;
use App\Service\PasswordGenerator;
use Exception;
use InvalidArgumentException;
use SimpleEnum\Exception\UnknownEumException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin")
 */
class AdminController extends MyController
{
    /**
     * @Route("/php", name="infos_php")
     */
    public function getInfo(): Response
    {
        ob_start();
        phpinfo();
        $str = ob_get_contents();
        ob_get_clean();
        return new Response($str);
    }

    /**
     * @Route("/", name="admin")
     * @param InscriptionRepository $rInsc
     * @return Response
     */
    public function index(InscriptionRepository $rInsc): Response
    {
        return $this->render('admin/index.html.twig', [
            'menu' => $this->getMenu(null, static::MENU_ADMIN),
            'en_attente' => count($rInsc->findByStatus())
        ]);
    }

    /**
     * @Route("/registration", name="registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $etablissement = new Etablissement();
        $form = $this->createForm(EtablissementType::class, $etablissement, ['isAdmin' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($etablissement, $etablissement->getPassword());
            $etablissement->setPassword($password);
            // Ajout d'un membre par défaut (Membre établissement)
            $partiesNom = explode(' ', $etablissement->getNom());
            $dftMembre = new Membre();
            $dftMembre->setDefaut(true);
            $dftMembre->setEmail("");
            $dftMembre->setPrenom(array_shift($partiesNom));
            $dftMembre->setNom(implode(' ', $partiesNom));
            $etablissement->addMembre($dftMembre);
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($dftMembre);
            $em->persist($etablissement);
            $em->flush();
            return $this->redirectToRoute('security_login');
        }
        return $this->render('registration/nouveau.html.twig', [
            'form' => $form->createView(),
            'menu' => $this->getMenu(null, static::MENU_ADMIN)
        ]);
    }

    /**
     * @Route("/inscriptions", name="inscriptions")
     * @param InscriptionRepository $rInscr
     * @return Response
     * @throws UnknownEumException
     */
    public function displayDemandesInscriptions(InscriptionRepository $rInscr): Response
    {
        return $this->render('inscription/liste.html.twig', [
            'inscriptions' => array_map(function (Inscription $inscription) {
                return new InscriptionRow($inscription);
            }, $rInscr->findByStatus()),
            'inscriptions_email' => array_map(function (Inscription $inscription) {
                return new InscriptionRow($inscription);
            }, $rInscr->findByStatus(InscriptionStatutEnum::A_VALIDER)),
            'menu' => $this->getMenu(null, static::MENU_ADMIN)
        ]);
    }

    /**
     * @Route("/inscription/{id<\d+>}/valider", name="accepter_inscription")
     * @param Inscription $inscription
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param PasswordGenerator $pwdGenerator
     * @param MailgunSender $sender
     * @return Response
     * @throws Exception
     */
    public function validerInscription(
        Inscription $inscription,
        UserPasswordEncoderInterface $passwordEncoder,
        PasswordGenerator $pwdGenerator,
        MailgunSender $sender
    ): Response
    {
        if (!$inscription->getContactEmail() || !filter_var($inscription->getContactEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("E-mail valide obligatoire !");
        }
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
        $etablissement->setPassword($passwordEncoder->encodePassword($etablissement, $pwdGenerator->generateSecuredPassword(10)));
        $etablissement->setResetPwdKey($pwdGenerator->generateRandomKey());
        // Ajout d'un membre par défaut (Membre établissement)
        $partiesNom = explode(' ', $etablissement->getNom());
        $dftMembre = new Membre();
        $dftMembre->setDefaut(true);
        $dftMembre->setEmail($etablissement->getEmail());
        $dftMembre->setPrenom(array_shift($partiesNom));
        $dftMembre->setNom(implode(' ', $partiesNom));
        $etablissement->addMembre($dftMembre);
        // On enregistre l'utilisateur dans la base
        $em = $this->getDoctrine()->getManager();
        $em->persist($dftMembre);
        $em->persist($etablissement);
        $inscription->setState(InscriptionStatutEnum::A_VALIDER);
        $em->persist($inscription);
        $em->flush();
        $contact = (new ContactDTO())
            ->setEmetteur("no-reply@web-project.fr")
            ->setTitre("Ouverture de compte validée")
            ->setDestinataire($etablissement->getEmail());
        $sender
            ->setTemplate('inscription_validee')
            //TODO - Générer link pour réinit mdp
            ->setTemplateVars(['link' => ''])
            ->envoyer($contact);
        $this->addFlash('success', "Compte $username créé !");
        return $this->redirectToRoute('inscriptions');
    }

    /**
     * @Route("/inscription/{id<\d+>}/refuser", name="refuser_inscription")
     * @param Inscription $inscription
     * @param MailgunSender $sender
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function refuserInscription(Inscription $inscription, MailgunSender $sender): Response
    {
        $inscription->setState(InscriptionStatutEnum::REFUSEE);
        $em = $this->getDoctrine()->getManager();
        $em->persist($inscription);
        $em->flush();
        $this->addFlash('danger', "Inscription refusée !");
        $contact = (new ContactDTO())
            ->setEmetteur("no-reply@web-project.fr")
            ->setTitre("Ouverture de compte refusée")
            ->setDestinataire($inscription->getContactEmail());
        $sender
            ->setTemplate('inscription_refusee')
            ->envoyer($contact);
        return $this->redirectToRoute('inscriptions');
    }
}
