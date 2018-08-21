<?php

namespace App\Controller;

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
     * @Route("/kermesse/{id}/activite/new", name="nouvelle_activite")
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
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
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
     * @Route("/activite/{id}/edit", name="editer_activite")
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
        $form = $this->createForm(ActiviteType::class, $activite);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
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
     * @Route("/activite/{id}/supprimer", name="supprimer_activite")
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
        }
        return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
    }

    /**
     * @Route("/activite/{id}", name="activite")
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
    public function index(Activite $activite, RecetteRepository $rRecette, DepenseRepository $rDepense, RecetteRowGenerator $rowGenerator, DepenseRowGenerator $dRowGenerator, ActiviteCardGenerator $actCardGenerator): Response
    {
        $totaux = $rRecette->getTotauxPourActivite($activite);
        $recette = $totaux['montant'];
        $nbTickets = $totaux['nombre_ticket'];
        $depense = $rDepense->getTotalPourActivite($activite);
        $totaux['montant'] = HFloat::getInstance($totaux['montant'] / 100.0)->getMontantFormatFrancais();
        return $this->render(
            'activite/index.html.twig',
            [
                'activite' => $activite,
                'recettes' => $rowGenerator->generateListPourActivite($activite),
                'depenses' => $dRowGenerator->generateList($activite),
                'card' => $actCardGenerator->generate($activite, $depense, $recette, $nbTickets),
                'total_recettes' => $totaux,
                'total_depenses' => HFloat::getInstance($depense / 100.0)->getMontantFormatFrancais(),
                'menu' => $this->getMenu($activite->getKermesse(), static::MENU_ACTIVITES)
            ]
        );
    }
}
