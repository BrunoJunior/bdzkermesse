<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Form\RecetteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RecetteController extends Controller
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
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'recette/nouvelle.html.twig',
            array('form' => $form->createView())
        );
    }
}
