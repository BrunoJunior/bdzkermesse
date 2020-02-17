<?php

namespace App\Controller;

use App\DataTransfer\DemandeInscription;
use App\Entity\Etablissement;
use App\Entity\Membre;
use App\Form\DemandeInscriptionType;
use App\Form\EtablissementType;
use App\Service\EnvoyerDemandeInscription;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RegistrationController extends MyController
{
    /**
     * @Route("/registration", name="registration")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function index(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $etablissement = new Etablissement();
        $form = $this->createForm(EtablissementType::class, $etablissement);
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
        return $this->render(
            'registration/nouveau.html.twig',
            array('form' => $form->createView())
        );
    }
    /**
     * @Route("/etablissement/edit", name="editer_etablissement")
     */
    public function editer(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $etablissement = $this->getUser();
        $oldPassword = $etablissement->getPassword();
        $form = $this->createForm(EtablissementType::class, $etablissement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($etablissement->getPassword() === '') {
                $password = $oldPassword;
            } else {
                $password = $passwordEncoder->encodePassword($etablissement, $etablissement->getPassword());
            }
            $etablissement->setPassword($password);
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($etablissement);
            $em->flush();
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'registration/edition.html.twig',
            array('form' => $form->createView(), 'menu' => $this->getMenu(null, static::MENU_ACCUEIL))
        );
    }

    /**
     * @Route("/inscription", name="demande_inscription")
     * @param Request $request
     * @param EnvoyerDemandeInscription $envoiDemande
     * @return RedirectResponse|Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function demandeInscription(Request $request, EnvoyerDemandeInscription $envoiDemande)
    {
        $demande = new DemandeInscription();
        $form = $this->createForm(DemandeInscriptionType::class, $demande);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Honeypot
            $isSpam = $request->get('name') || $request->get('phone');
            // Si c'est du spam, on fait croire que c'est OK, mais on ne fait rien
            if ($isSpam || $envoiDemande->run($demande) > 0) {
                $this->addFlash('success', "Votre demande a bien été transmise ! Nous vous contacterons dans les plus brefs délais !");
                return $this->redirectToRoute('security_login');
            } else {
                $this->addFlash('error', "Erreur lors de la transmission de votre demande ! Veuillez réessayer ultérieurement !");
            }
        }
        return $this->render('registration/index.html.twig', ['form' => $form->createView()]);
    }
}
