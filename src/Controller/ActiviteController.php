<?php

namespace App\Controller;

use App\DataTransfer\Colonne;
use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Form\ActiviteType;
use App\Helper\HFloat;
use App\Repository\DepenseRepository;
use App\Repository\RecetteRepository;
use App\Service\ActiviteCardGenerator;
use App\Service\DepenseRowGenerator;
use App\Service\RecetteRowGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ActiviteController extends MyController
{

    /**
     * @Route("/kermesses/{id}/activites/new", name="nouvelle_activite")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @return Response
     */
    public function nouvelleActivite(Kermesse $kermesse, Request $request):Response
    {
        $activite = new Activite();
        $activite->setCaisseCentrale(false);
        $activite->setKermesse($kermesse);
        $activite->setAccepteMonnaie(true);
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
            $this->addFlash("success", "Activité " . $activite->getNom() . ' créée !');
            return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'activite/nouvelle.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/activites/{id}/edit", name="editer_activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param Request $request
     * @return Response
     */
    public function editerActivite(Activite $activite, Request $request):Response
    {
        if ($activite->isCaisseCentrale()) {
            $this->redirectToRoute('kermesse', ['id' => $activite->getKermesse()->getId()]);
        }
        $activite->setAccepteMonnaie(true);
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
            $this->addFlash("success", "Activité " . $activite->getNom() . ' mise à jour !');
            return $this->redirectToRoute('kermesse', ['id' => $activite->getKermesse()->getId()]);
        }
        return $this->render(
            'activite/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($activite->getKermesse(), static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/activites/{id}/supprimer", name="supprimer_activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @return Response
     */
    public function supprimerActivite(Activite $activite):Response
    {
        $kermesse = $activite->getKermesse();
        if (!$activite->isCaisseCentrale()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activite);
            $em->flush();
            $this->addFlash("success", "Activité " . $activite->getNom() . ' supprimée !');
        }
        return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
    }

    /**
     * @Route("/activites/{id}", name="activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param RecetteRepository $rRecette
     * @param DepenseRepository $rDepense
     * @param RecetteRowGenerator $rowGenerator
     * @param DepenseRowGenerator $dRowGenerator
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function index(Activite $activite, RecetteRepository $rRecette, DepenseRepository $rDepense, RecetteRowGenerator $rowGenerator, DepenseRowGenerator $dRowGenerator, Request $request): Response
    {
        $order = $request->get('order', 'date');
        $colonnes = [
            new Colonne('id', '#'),
            new Colonne('date', 'Date', 'fas fa-calendar'),
            new Colonne('libelle', 'Libellé', 'fas fa-tag'),
            new Colonne('nombre_ticket', 'Nombre de tickets', 'fas ticket-alt'),
            new Colonne('montant', 'Montant', 'fas fa-euro-sign'),
            new Colonne('actions', 'Actions', 'fab fa-telegram-plane')
        ];
        $totaux = $rRecette->getTotauxPourActivite($activite);
        $depense = $rDepense->getTotalPourActivite($activite);
        $totaux['montant'] = HFloat::getInstance($totaux['montant'] / 100.0)->getMontantFormatFrancais();
        return $this->render(
            'activite/index.html.twig',
            [
                'activite' => $activite,
                'recettes' => $rowGenerator->generateListPourActivite($activite, $order),
                'depenses' => $dRowGenerator->generateList($activite),
                'total_recettes' => $totaux,
                'total_depenses' => HFloat::getInstance($depense / 100.0)->getMontantFormatFrancais(),
                'menu' => $this->getMenu($activite->getKermesse(), static::MENU_ACTIVITES),
                'colonnes' => $colonnes,
                'order' => $order
            ]
        );
    }

    /**
     * @Route("/activites/{id}/card", name="carte_activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param RecetteRepository $rRecette
     * @param DepenseRepository $rDepense
     * @param ActiviteCardGenerator $actCardGenerator
     * @return Response
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function card(Activite $activite, RecetteRepository $rRecette, DepenseRepository $rDepense, ActiviteCardGenerator $actCardGenerator):Response
    {
        $totaux = $rRecette->getTotauxPourActivite($activite);
        $recette = $totaux['montant'];
        $nbTickets = $totaux['nombre_ticket'];
        $depense = $rDepense->getTotalPourActivite($activite);
        $totaux['montant'] = HFloat::getInstance($totaux['montant'] / 100.0)->getMontantFormatFrancais();
        return $this->render(
            'activite/card.html.twig',
            [
                'card' => $actCardGenerator->generate($activite, $depense, $recette, $nbTickets)
            ]);
    }
}
