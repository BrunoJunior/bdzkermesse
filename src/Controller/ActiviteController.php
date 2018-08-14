<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Form\ActiviteType;
use App\Helper\Breadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ActiviteController extends MyController
{

    /**
     * @Route("/kermesse/{id}/activite/new", name="nouvelle_activite")
     */
    public function nouvelleActivite(Kermesse $kermesse, Request $request)
    {
        $activite = new Activite();
        $activite->setCaisseCentrale(false);
        $activite->setKermesse($kermesse);
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
            return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'activite/nouvelle.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/activite/{id}/edit", name="editer_activite")
     */
    public function editerActivite(Activite $activite, Request $request)
    {
        if ($activite->isCaisseCentrale()) {
            $this->redirectToRoute('kermesse', ['id' => $activite->getKermesse()->getId()]);
        }
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
            return $this->redirectToRoute('kermesse', ['id' => $activite->getKermesse()->getId()]);
        }
        $breadcrumb = new Breadcrumb();
        return $this->render(
            'activite/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($activite->getKermesse(), static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/activite/{id}/supprimer", name="supprimer_activite")
     */
    public function supprimerActivite(Activite $activite)
    {
        $kermesse = $activite->getKermesse();
        if (!$activite->isCaisseCentrale()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activite);
            $em->flush();
        }
        return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
    }
}
