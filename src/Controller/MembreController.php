<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Form\MembreType;
use App\Helper\Breadcrumb;
use App\Helper\MenuLink;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MembreController extends MyController
{

    /**
     * @return Breadcrumb
     */
    private function getMenu() {
        return $menu = Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance('Accueil', 'home', $this->generateUrl('index')))
            ->addLink($this->getKermessesMenuLink())
            ->addLink(MenuLink::getInstance('Membres', 'users', $this->generateUrl('membres'))->setActive());
    }

    /**
     * @Route("/membre", name="membres")
     */
    public function index()
    {
        return $this->render('membre/index.html.twig', [
            'membres' => $this->getUser()->getMembres(),
            'menu' => $this->getMenu()
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
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'membre/nouveau.html.twig',
            array('form' => $form->createView(),
                'menu' => $this->getMenu())
        );
    }
}
