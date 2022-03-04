<?php

namespace App\Controller;

use App\DataTransfer\DemandeInscription;
use App\Form\DemandeInscriptionType;
use App\Service\EnvoyerDemandeInscription;
use App\Service\InscriptionManager;
use App\Service\PasswordResetter;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class InscriptionController
 * @package App\Controller
 * @author bruno <bdesprez@thalassa.fr>
 * @Route("/inscription")
 */
class InscriptionController extends MyController
{

    /**
     * @Route("/", name="demande_inscription")
     * @param Request $request
     * @param EnvoyerDemandeInscription $envoiDemande
     * @return RedirectResponse|Response
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
        return $this->render('inscription/index.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/oubli-mdp", name="oubli_mdp")
     * @param Request $request
     * @param InscriptionManager $manager
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function oubliMotDePasse(Request $request, InscriptionManager $manager)
    {
        $form = $manager->sendForgotPasswordMail($request);
        if ($form === null) {
            $this->addFlash('success', "Si votre identifiant de connexion est connu, vous recevrez un e-mail de réinitialisation de mot de passe !");
            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/oublipwd.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/oubli-identifiant", name="oubli_identifiant")
     * @param Request $request
     * @param InscriptionManager $manager
     * @return RedirectResponse|Response
     */
    public function oubliIdentifiant(Request $request, InscriptionManager $manager)
    {
        $form = $manager->sendForgotPasswordIdentifiant($request);
        if ($form === null) {
            $this->addFlash('success', "Si votre adresse e-mail est connue, vous recevrez un e-mail contenant vos identifiants de connexion !");
            return $this->redirectToRoute('security_login');
        }
        return $this->render('security/oubliusername.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}/validation/{key}", name="validation_email")
     * @param Request $request
     * @param int $id
     * @param string $key
     * @param PasswordResetter $resetter
     * @return Response
     * @throws NonUniqueResultException
     */
    public function validerEmail(Request $request, int $id, string $key, PasswordResetter $resetter): Response {
        $form = $resetter->validerEmail($request, $id, $key);
        if ($form === null) { // Formulaire null = il a été traité
            $this->addFlash('success', "Votre compte a bien été validé !");
            return $this->redirectToRoute('security_login');
        }
        return $this->render(
            'registration/reset_password.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/{id}/reset-password/{key}", name="reset_pwd")
     * @param Request $request
     * @param int $id
     * @param string $key
     * @param PasswordResetter $resetter
     * @return Response
     * @throws NonUniqueResultException
     */
    public function resetPassword(Request $request, int $id, string $key, PasswordResetter $resetter): Response {
        $form = $resetter->reset($request, $id, $key);
        if ($form === null) { // Formulaire null = il a été traité
            $this->addFlash('success', "Votre mot de passe a été mis à jour !");
            return $this->redirectToRoute('security_login');
        }
        return $this->render(
            'registration/reset_password.html.twig',
            array('form' => $form->createView())
        );
    }
}
