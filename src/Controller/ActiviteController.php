<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Form\ActiviteType;
use App\Helper\Breadcrumb;
use App\Helper\MenuLink;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ActiviteController extends MyController
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
        $breadcrumb = new Breadcrumb();
        return $this->render(
            'activite/nouvelle.html.twig',
            [
                'form' => $form->createView(),
                'kermesse' => $kermesse,
                'menu' => $this->getMenu($kermesse)
            ]
        );
    }

    /**
     * @Route("/activite/{id}/supprimer", name="supprimer_activite")
     */
    public function supprimerActivite(Activite $activite)
    {
        $kermesse = $activite->getKermesse();
        $em = $this->getDoctrine()->getManager();
        $em->remove($activite);
        $em->flush();
        return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
    }
}
