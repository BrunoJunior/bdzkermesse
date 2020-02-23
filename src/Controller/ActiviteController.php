<?php

namespace App\Controller;

use App\DataTransfer\ActivitePlanning;
use App\DataTransfer\Colonne;
use App\DataTransfer\PlageHoraire;
use App\Entity\Activite;
use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Form\ActiviteType;
use App\Helper\Breadcrumb;
use App\Helper\HFloat;
use App\Repository\ActiviteRepository;
use App\Repository\DepenseRepository;
use App\Repository\RecetteRepository;
use App\Service\ActiviteCardGenerator;
use App\Service\DepenseRowGenerator;
use App\Service\RecetteRowGenerator;
use DateTimeImmutable;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ActiviteController extends MyController
{

    /**
     * @param Request $request
     * @param string $action
     * @param Activite|null $activite
     * @param Kermesse|null $kermesse
     * @return Response
     */
    private function saveActivite(Request $request, string $action, ?Activite $activite = null, ?Kermesse $kermesse = null): Response
    {
        $activite = $activite ?: new Activite();
        $kermesse = $activite->getKermesse() ?: $kermesse;
        $activite->setCaisseCentrale($activite->isCaisseCentrale() ?: false);
        $activite->setKermesse($kermesse);
        $activite->setEtablissement($this->getEtablissement());
        if ($activite->getKermesse()) {
            $activite->setAccepteMonnaie(true);
        } else {
            $activite->setAccepteSeulementMonnaie();
        }
        $form = $this->createForm(ActiviteType::class, $activite, ['withKermesse' => $kermesse !== null, 'action' => $action]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($activite);
            $em->flush();
            return $this->reponseModal();
        }
        return $this->render(
            'activite/form.html.twig',
            ['form' => $form->createView(), 'kermesse' => $kermesse]
        );
    }

    /**
     * @Route("/kermesses/{id<\d+>}/activites/new", name="nouvelle_activite")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @return Response
     */
    public function nouvelleActivite(Kermesse $kermesse, Request $request): Response
    {
        return $this->saveActivite(
            $request,
            $this->generateUrl('nouvelle_activite', ['id' => $kermesse->getId()]),
            null,
            $kermesse
        );
    }

    /**
     * @Route("/activites/new", name="nouvelle_autre_activite")
     * @param Request $request
     * @return Response
     */
    public function nouvelleAutreActivite(Request $request): Response
    {
        return $this->saveActivite($request, $this->generateUrl('nouvelle_autre_activite'));
    }

    /**
     * @Route("/activites/{id<\d+>}/edit", name="editer_activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param Request $request
     * @return Response
     */
    public function editerActivite(Activite $activite, Request $request): Response
    {
        return $this->saveActivite($request, $this->generateUrl('editer_activite', ['id' => $activite->getId()]), $activite);
    }

    /**
     * @param Kermesse|null $kermesse
     * @return Response
     */
    private function redirectToKermesseOuAutre(?Kermesse $kermesse): Response
    {
        return $kermesse
            ? $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()])
            : $this->redirectToRoute('lister_actions');
    }

    /**
     * @param Kermesse|null $kermesse
     * @return Breadcrumb
     */
    private function getMenuKermesseOuAutre(?Kermesse $kermesse): Breadcrumb
    {
        return $this->getMenu($kermesse, $kermesse ? static::MENU_ACTIVITES : static::MENU_ACTIVITES_AUTRES);
    }

    /**
     * @Route("/activites/{id<\d+>}/supprimer", name="supprimer_activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @return Response
     */
    public function supprimerActivite(Activite $activite): Response
    {
        $kermesse = $activite->getKermesse();
        if (!$activite->isCaisseCentrale()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($activite);
            $em->flush();
            $this->addFlash("success", "Activité " . $activite->getNom() . ' supprimée !');
        }
        return $this->redirectToKermesseOuAutre($kermesse);
    }

    /**
     * @Route("/activites/{id<\d+>}", name="activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param RecetteRepository $rRecette
     * @param DepenseRepository $rDepense
     * @param RecetteRowGenerator $rowGenerator
     * @param DepenseRowGenerator $dRowGenerator
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @throws NoResultException
     * @throws NonUniqueResultException
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
                'menu' => $this->getMenuKermesseOuAutre($activite->getKermesse()),
                'colonnes' => $colonnes,
                'order' => $order
            ]
        );
    }

    /**
     * @Route("/activites/{id<\d+>}/card", name="carte_activite")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param RecetteRepository $rRecette
     * @param DepenseRepository $rDepense
     * @param ActiviteCardGenerator $actCardGenerator
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function card(Activite $activite, RecetteRepository $rRecette, DepenseRepository $rDepense, ActiviteCardGenerator $actCardGenerator): Response
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

    /**
     * @Route("/activites/{id<\d+>}/benevoles", name="gerer_benevoles")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @return Response
     */
    public function gererBenevoles(Activite $activite): Response
    {
        return $this->render('activite/benevoles.html.twig', [
            'activite' => ActivitePlanning::createFromEntity($activite),
            'menu' => $this->getMenu($activite->getKermesse(), static::MENU_ACTIVITES)
        ]);
    }

    /**
     * @Route("/actions/{annee<\d+>?}", name="lister_actions")
     * @param int|null $annee
     * @param ActiviteRepository $rActivite
     * @return Response
     * @throws Exception
     */
    public function actions(?int $annee, ActiviteRepository $rActivite): Response
    {
        $etablissement = $this->getUser();
        if (!$etablissement instanceof Etablissement) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        $now = new DateTimeImmutable();
        $date = $now;
        if ($annee) {
            $date = $now->setDate($annee, (int) $now->format('n'), (int) $now->format('d'));
        }
        $periode = PlageHoraire::createAnneeScolaire($date);
        return $this->render(
            'activite/actions.html.twig',
            [
                'activites' => $rActivite->getListeAutres($etablissement, $date),
                'periode' => $periode,
                'annee' => (int) $date->format('Y'),
                'courante' => $now >= $periode->getDebut() && $now < $periode->getFin(),
                'menu' => $this->getMenuKermesseOuAutre(null)
            ]
        );
    }
}
