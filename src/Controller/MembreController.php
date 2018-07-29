<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\MembreType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MembreController extends Controller
{
    /**
     * @Route("/membre/new", name="nouveau_membre")
     */
    public function nouveauMembre(Request $request)
    {
        $membre = new Membre();
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $membre->setEtablissement($this->getUser());
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($membre);
            $em->flush();
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'membre/nouveau.html.twig',
            array('form' => $form->createView())
        );
    }
}
