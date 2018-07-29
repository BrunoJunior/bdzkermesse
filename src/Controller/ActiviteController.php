<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Form\ActiviteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ActiviteController extends Controller
{
    /**
     * @Route("/kermesse/{id}/activite/new", name="nouvelle_activite")
     */
    public function nouvelleActivite(Kermesse $kermesse, Request $request)
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activite->setKermesse($kermesse);
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
            return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'activite/nouvelle.html.twig',
            array('form' => $form->createView())
        );
    }
}
