<?php

namespace App\Controller;

use App\Entity\Etablissement;
use App\Service\EtablissementUpdater;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RegistrationController
 * @package App\Controller
 * @author bruno <bdesprez@thalassa.fr>
 */
class RegistrationController extends MyController
{

    /**
     * @Route("/etablissement/edit", name="editer_etablissement")
     * @param Request $request
     * @param EtablissementUpdater $updater
     * @return Response
     */
    public function editer(Request $request, EtablissementUpdater $updater): Response
    {
        $etab = $this->getUser();
        if (!$etab instanceof Etablissement) {
            throw new \InvalidArgumentException("L'utilisateur DOIT être un établissement !");
        }
        $form = $updater->traiterDemande($request, $etab, $this->generateUrl('editer_etablissement'));
        return null === $form ? $this->reponseModal() : $this->render(
            'registration/edition_modal.html.twig',
            array('form' => $form->createView())
        );
    }
}
