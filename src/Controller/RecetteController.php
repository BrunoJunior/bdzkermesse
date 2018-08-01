<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Form\RecetteType;
use App\Helper\Breadcrumb;
use App\Helper\MenuLink;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecetteController extends MyController
{

    /**
     * @param Kermesse $kermesse
     * @return Breadcrumb
     */
    private function getMenu(Kermesse $kermesse) {
        return Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance('Accueil', 'home', $this->generateUrl('index')))
            ->addLink($this->getKermessesMenuLink($kermesse))
            ->addLink(MenuLink::getInstance('Membres', 'users', $this->generateUrl('membres')));
    }

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
            [
                'form' => $form->createView(),
                'kermesse' => $kermesse,
                'menu' => $this->getMenu($kermesse)
            ]
        );
    }
}
