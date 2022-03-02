<?php

namespace App\Controller;

use App\DataTransfer\DemandeInscription;
use App\Form\DemandeInscriptionType;
use App\Service\EnvoyerDemandeInscription;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends MyController
{

    /**
     * @Route("/inscription", name="demande_inscription")
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
}
