<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Form\RecetteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecetteController extends MyController
{
    /**
     * @Route("/kermesse/{id}/recette/new", name="nouvelle_recette")
     */
    public function nouvelleRecette(Kermesse $kermesse, Request $request)
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette, ['kermesse' => $kermesse]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recette);
            $em->flush();
            return $this->redirectToRoute('liste_recettes', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'recette/nouvelle.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, static::MENU_RECETTES)
            ]
        );
    }

    /**
     * @Route("/recette/{id}/edit", name="editer_recette")
     */
    public function editerRecette(Recette $recette, Request $request)
    {
        $form = $this->createForm(RecetteType::class, $recette, ['kermesse' => $recette->getActivite()->getKermesse()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recette);
            $em->flush();
            return $this->redirectToRoute('liste_recettes', ['id' => $recette->getActivite()->getKermesse()->getId()]);
        }
        return $this->render(
            'recette/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($recette->getActivite()->getKermesse(), static::MENU_RECETTES)
            ]
        );
    }
}
