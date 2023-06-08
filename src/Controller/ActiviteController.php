<?php

namespace App\Controller;

use App\DataTransfer\ActivitePlanning;
use App\DataTransfer\Colonne;
use App\DataTransfer\PlageHoraire;
use App\Entity\Activite;
use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Entity\TypeActivite;
use App\Exception\ServiceException;
use App\Form\ActiviteType;
use App\Helper\Breadcrumb;
use App\Helper\HFloat;
use App\Repository\ActiviteRepository;
use App\Repository\DepenseRepository;
use App\Repository\RecetteRepository;
use App\Repository\TypeActiviteRepository;
use App\Service\ActiviteCardGenerator;
use App\Service\ActiviteMover;
use App\Service\DepenseRowGenerator;
use App\Service\KermesseService;
use App\Service\RecetteRowGenerator;
use DateTimeImmutable;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ActiviteController extends MyController
{
    /**
     * @var ActiviteRepository
     */
    private $rActivite;

    /**
     * @var TypeActiviteRepository
     */
    private $rTypeActivite;

    /**
     * @var ManagerRegistry
     */
    private $em;

    /**
     * @param LoggerInterface $logger
     * @param ActiviteRepository $rActivite
     * @param TypeActiviteRepository $rTypeActivite
     * @param ManagerRegistry $em
     */
    public function __construct(LoggerInterface $logger, ActiviteRepository $rActivite, TypeActiviteRepository $rTypeActivite, ManagerRegistry $em)
    {
        parent::__construct($logger);
        $this->rActivite = $rActivite;
        $this->rTypeActivite = $rTypeActivite;
        $this->em = $em;
    }

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
        // For a new activity we take the next available position
        $activite->setOrdre($activite->getOrdre() ?: $this->rActivite->getNextPosition($kermesse));
        $activite->setCaisseCentrale($activite->isCaisseCentrale() ?: false);
        $activite->setKermesse($kermesse);
        $activite->setEtablissement($this->getEtablissement());
        if ($activite->getKermesse()) {
            $activite->setAccepteMonnaie(true);
        } else {
            $activite->setAccepteSeulementMonnaie();
        }
        $availableTypes = $this->rTypeActivite->findByEtablissement($this->getEtablissement());
        $form = $this->createForm(ActiviteType::class, $activite, [
            'withKermesse' => $kermesse !== null,
            'action' => $action,
            'availableTypes' => $availableTypes,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $typeActivite = $activite->getType();
            // -1 => Create a new type
            if ($typeActivite !== null && $typeActivite->getId() === -1) {
                $newTypeName = $form->get("new_type_activite")->getData();
                $newType = null;
                // New type name not defined => no type
                if ($newTypeName) {
                    // Check if another type with the same name exists
                    $newType = $this->rTypeActivite->findOneByNom($this->getEtablissement(), $newTypeName);
                    // Insert a new type for my etablissement
                    if (!$newType) {
                        $newType = (new TypeActivite())
                            ->setEtablissement($this->getEtablissement())
                            ->setNom($newTypeName);
                        $this->em->getManager()->persist($newType);
                    }
                }
                $activite->setType($newType);
            }

            $em = $this->em->getManager();
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
     * @param KermesseService $sKermesse
     * @return Response
     * @throws ServiceException
     */
    public function supprimerActivite(Activite $activite, KermesseService $sKermesse): Response
    {
        if ($activite->isCaisseCentrale()) {
            throw new ServiceException("Vous n'êtes pas autorisé à faire cela !");
        }
        // If one of the activities does not have an order, we compute all of them (for one kermesse)
        $kermesse = $activite->getKermesse();
        $sKermesse->initialiserOrdreActivites($kermesse);
        $em = $this->em->getManager();
        // Before the activity deletion we move upward activities which are after the deleted one
        $toMove = $this->rActivite->findWillMove($kermesse->getId(), $activite->getOrdre());
        foreach ($toMove as $activiteToMove) {
            if ($activiteToMove->getId() !== $activite->getId()) {
                $activiteToMove->setOrdre($activiteToMove->getOrdre() - 1);
                $em->persist($activiteToMove);
            }
        }
        $em->remove($activite);
        $em->flush();
        return $this->reponseModal("Activité " . $activite->getNom() . ' supprimée !');
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
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function index(Activite $activite, RecetteRepository $rRecette, DepenseRepository $rDepense, RecetteRowGenerator $rowGenerator, DepenseRowGenerator $dRowGenerator, Request $request): Response
    {
        $order = $request->get('order', 'date');
        $forKermesse = $activite->getKermesse() !== null;
        $colonnes = [
            "id" => new Colonne('id', '#'),
            "date" => new Colonne('date', 'Date', 'fas fa-calendar'),
            "libelle" => new Colonne('libelle', 'Libellé', 'fas fa-tag'),
            "nombre_ticket" => new Colonne('nombre_ticket', 'Nombre de tickets', 'fas ticket-alt'),
            "montant" => new Colonne('montant', 'Montant', 'fas fa-euro-sign'),
            "actions" => new Colonne('actions', 'Actions', 'fab fa-telegram-plane')
        ];
        // Le nombre de ticket n'est utile que pour une activité de kermesse
        if (!$forKermesse) {
            unset($colonnes['nombre_ticket']);
        }
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
                'order' => $order,
                'for_kermesse' => $forKermesse,
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
     * Move an activity and return all moved ones (in json format)
     * @Route("/activites/{id<\d+>}/moveTo/{position}", name="move_activity")
     * @Security("activite.isProprietaire(user)")
     * @param Activite $activite
     * @param int $position
     * @param ActiviteMover $mover
     * @return Response
     */
    public function move(Activite $activite, int $position, ActiviteMover $mover): Response
    {
        return $this->json(['moved' => $mover->moveActivityTo($activite, $position)]);
    }

    /**
     * @Route("/actions/{annee<\d+>?}", name="lister_actions")
     * @param int|null $annee
     * @return Response
     * @throws Exception
     */
    public function actions(?int $annee): Response
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
                'activites' => $this->rActivite->getListeAutres($etablissement, $date),
                'periode' => $periode,
                'annee' => (int) $date->format('Y'),
                'courante' => $now >= $periode->getDebut() && $now < $periode->getFin(),
                'menu' => $this->getMenuKermesseOuAutre(null)
            ]
        );
    }
}
