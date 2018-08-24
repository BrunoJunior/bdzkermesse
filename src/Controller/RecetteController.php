<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Form\RecetteType;
use App\Repository\ActiviteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class RecetteController extends MyController
{
    /**
     * @Route("/kermesses/{id}/recettes/new", name="nouvelle_recette")
     * @Security("kermesse.isProprietaire(user)")
     */
    public function nouvelleRecette(Kermesse $kermesse, Request $request, ActiviteRepository $rActivite)
    {
        $recette = new Recette();
        $form = $this->createForm(RecetteType::class, $recette, [
            'kermesse' => $kermesse,
            'acceptent_tickets' => $rActivite->getListeIdAccepteTickets($kermesse)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recette);
            $em->flush();
            $this->addFlash("success", "Recette enregistrée avec succès !");
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
     * @Route("/recettes/{id}/edit", name="editer_recette")
     * @Security("recette.isProprietaire(user)")
     */
    public function editerRecette(Recette $recette, Request $request, ActiviteRepository $rActivite)
    {
        $kermesse = $recette->getActivite()->getKermesse();
        $form = $this->createForm(RecetteType::class, $recette, [
            'kermesse' => $recette->getActivite()->getKermesse(),
            'acceptent_tickets' => $rActivite->getListeIdAccepteTickets($kermesse)
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($recette);
            $em->flush();
            $this->addFlash("success", "Recette enregistrée avec succès !");
            return $this->redirectToRoute('liste_recettes', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'recette/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, static::MENU_RECETTES)
            ]
        );
    }

    /**
     * @Route("/recettes/{id}/supprimer", name="supprimer_recette")
     * @Security("recette.isProprietaire(user)")
     */
    public function supprimerRecette(Recette $recette)
    {
        $kermesse = $recette->getActivite()->getKermesse();
        $em = $this->getDoctrine()->getManager();
        $em->remove($recette);
        $em->flush();
        $this->addFlash("success", "Recette suprimmée !");
        return $this->redirectToRoute('liste_recettes', ['id' => $kermesse->getId()]);
    }
}
