<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\MembreType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MembreController extends MyController
{
    /**
     * @Route("/membre", name="membres")
     */
    public function index():Response
    {
        return $this->render('membre/index.html.twig', [
            'membres' => $this->getUser()->getMembres(),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }

    /**
     * @Route("/membre/new", name="nouveau_membre")
     * @param Request $request
     * @return Response
     */
    public function nouveauMembre(Request $request):Response
    {
        $membre = new Membre();
        $membre->setEtablissement($this->getUser());
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
            'membre/nouveau.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu(null, static::MENU_MEMBRES))
        );
    }

    /**
     * @Route("/membre/{id}/edit", name="editer_membre")
     * @Security("membre.isProprietaire(user)")
     * @param Membre $membre
     * @param Request $request
     * @return Response
     */
    public function editerMembre(Membre $membre, Request $request):Response
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
