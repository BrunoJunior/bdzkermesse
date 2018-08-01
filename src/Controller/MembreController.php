<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\MembreType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MembreController extends MyController
{
    /**
     * @Route("/membre", name="membres")
     */
    public function index()
    {
        return $this->render('membre/index.html.twig', [
            'membres' => $this->getUser()->getMembres(),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }

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
            return $this->redirectToRoute('membres');
        }
        return $this->render(
            'membre/nouveau.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_MEMBRES))
        );
    }

    /**
     * @Route("/membre/{id}/edit", name="editer_membre")
     */
    public function editerMembre(Membre $membre, Request $request)
    {
        $form = $this->createForm(MembreType::class, $membre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($membre);
            $em->flush();
            return $this->redirectToRoute('membres');
        }
        return $this->render(
            'membre/edition.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_MEMBRES))
        );
    }
}
