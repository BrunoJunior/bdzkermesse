<?php

namespace App\Controller;

use App\Business\KermesseBusiness;
use App\DataTransfer\Colonne;
use App\DataTransfer\ContactDTO;
use App\DataTransfer\Planning;
use App\DataTransfer\RecapitulatifBenevole;
use App\Entity\Kermesse;
use App\Exception\BusinessException;
use App\Exception\ServiceException;
use App\Form\KermesseType;
use App\Form\MembresKermesseType;
use App\Helper\HFloat;
use App\Repository\RecetteRepository;
use App\Service\EmailSender;
use App\Service\KermesseCardGenerator;
use App\Service\KermesseService;
use App\Service\RecetteRowGenerator;
use App\Service\RemboursementRowGenerator;
use App\Service\TicketRowGenerator;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Psr\Log\LoggerInterface;
use SimpleEnum\Exception\UnknownEumException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class KermesseController extends MyController
{
    /**
     * @var KermesseBusiness
     */
    private $business;

    /**
     * KermesseController constructor.
     * @param KermesseBusiness $business
     * @param LoggerInterface $logger
     */
    public function __construct(KermesseBusiness $business, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->business = $business;
    }

    /**
     * @param Request $request
     * @param KermesseService $sKermesse
     * @param Kermesse $kermesse
     * @return Response
     * @throws NonUniqueResultException
     * @throws ServiceException
     */
    private function saveKermesse(Request $request, KermesseService $sKermesse, Kermesse $kermesse): Response
    {
        $route = $kermesse->getId() ? 'editer_kermesse' : 'nouvelle_kermesse';
        $params = $kermesse->getId() ? ['id' => $kermesse->getId()] : [];
        $kermesse->setEtablissement($this->getEtablissement());
        $form = $this->createForm(KermesseType::class, $kermesse, ['action' => $this->generateUrl($route, $params)]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // On enregistre l'utilisateur dans la base
            $em = $this->getDoctrine()->getManager();
            $em->persist($kermesse);
            $em->flush();
            $sKermesse->setKermesse($kermesse)->gererCaisseCentrale();
            return $this->reponseModal();
        }
        return $this->render('kermesse/form.html.twig', ['form' => $form->createView(), 'edition' => $kermesse->getId()]);
    }

    /**
     * @Route("/kermesses/new", name="nouvelle_kermesse")
     * @param Request $request
     * @param KermesseService $sKermesse
     * @return Response
     * @throws ServiceException
     * @throws NonUniqueResultException
     */
    public function nouvelleKermesse(Request $request, KermesseService $sKermesse): Response
    {
        return $this->saveKermesse($request, $sKermesse, new Kermesse());
    }

    /**
     * @Route("/kermesses/{id}/edit", name="editer_kermesse")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @param KermesseService $sKermesse
     * @return Response
     * @throws ServiceException
     * @throws NonUniqueResultException
     */
    public function editerKermesse(Kermesse $kermesse, Request $request, KermesseService $sKermesse): Response
    {
        return $this->saveKermesse($request, $sKermesse, $kermesse);
    }

    /**
     * @Route("/kermesses/{id}/dupliquer", name="dupliquer_kermesse")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @param KermesseService $sKermesse
     * @param EntityManagerInterface $entityManager
     * @return Response
     * @throws Exception
     */
    public function dupliquerKermesse(Kermesse $kermesse, Request $request, KermesseService $sKermesse, EntityManagerInterface $entityManager): Response
    {
        $nouvelleKermesse = new Kermesse();
        $nouvelleKermesse->setEtablissement($this->getEtablissement());
        $nouvelleKermesse->setMontantTicket($kermesse->getMontantTicket());
        $form = $this->createForm(KermesseType::class, $nouvelleKermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Débuter une trasaction, tout passe ou rien ne passe
            try {
                $entityManager->beginTransaction();
                $entityManager->persist($nouvelleKermesse);
                $entityManager->flush();
                $sKermesse->setKermesse($nouvelleKermesse)->dupliquerInfos($kermesse);
                $entityManager->commit();
                return $this->reponseModal("Kermesse " . $nouvelleKermesse->getAnnee() . ' créée à partir de la kermesse ' . $kermesse->getAnnee());
            } catch (Exception $exc) {
                $entityManager->rollback();
                throw $exc;
            }
        }
        return $this->render('kermesse/form.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/kermesses/{id}", name="kermesse", requirements={"id"="\d+"})
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @return Response
     */
    public function index(Kermesse $kermesse): Response
    {
        return $this->render(
            'kermesse/index.html.twig',
            [
                'kermesse' => $kermesse,
                'activites' => $kermesse->getActivites(),
                'menu' => $this->getMenu($kermesse, static::MENU_ACTIVITES)
            ]
        );
    }

    /**
     * @Route("/kermesses/{id}/membres_actifs", name="membres_actifs")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param Request $request
     * @return Response
     */
    public function definirMembresActifs(Kermesse $kermesse, Request $request): Response
    {
        $form = $this->createForm(MembresKermesseType::class, $kermesse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // On enregistre l'utilisateur dans la base
            $em->persist($kermesse);
            $em->flush();
            $this->addFlash("success", "Les membres actifs de la kermesse  " . $kermesse->getAnnee() . ' ont été définis !');
        }
        return $this->render(
            'kermesse/membres.html.twig',
            [
                'form' => $form->createView(),
                'kermesse' => $kermesse,
                'menu' => $this->getMenu($kermesse, static::MENU_MEMBRES_ACTIFS)
            ]
        );
    }

    /**
     * @Route("/kermesses/{id}/tickets", name="liste_tickets")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param TicketRowGenerator $ticketGenerator
     * @param Request $request
     * @return Response
     * @throws DBALException
     * @throws UnknownEumException
     */
    public function listeTickets(Kermesse $kermesse, TicketRowGenerator $ticketGenerator, Request $request): Response
    {
        $order = $request->get('order', 'date');
        $colonnes = [
            new Colonne('id', '#', '', true),
            new Colonne('etat', 'État', 'fas fa-question-circle', true),
            new Colonne('date', 'Date', 'fas fa-calendar', true),
            new Colonne('membre', 'Acheteur', 'fas fa-user', true, true),
            new Colonne('numero', 'N°', 'fas fa-barcode', true, true),
            new Colonne('fournisseur', 'Fournisseur', 'fas fa-truck', true, true),
            new Colonne('montant', 'Montant', 'fas fa-receipt', true, true),
            new Colonne('activites', 'Activités liées', 'fas fa-link'),
            new Colonne('actions', 'Actions', 'fab fa-telegram-plane'),
        ];
        return $this->render(
            'kermesse/tickets.html.twig',
            [
                'kermesse' => $kermesse,
                'rows' => $ticketGenerator->generateList($kermesse, $order),
                'menu' => $this->getMenu($kermesse, static::MENU_TICKETS),
                'colonnes' => $colonnes,
                'order' => $order
            ]
        );
    }

    /**
     * @Route("/kermesses/{id}/recettes", name="liste_recettes")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param RecetteRepository $rRecette
     * @param RecetteRowGenerator $rowGenerator
     * @param Request $request
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function listeRecettes(Kermesse $kermesse, RecetteRepository $rRecette, RecetteRowGenerator $rowGenerator, Request $request): Response
    {
        $order = $request->get('order', 'date');
        $colonnes = [
            new Colonne('id', '#', '', true),
            new Colonne('date', 'Date', 'fas fa-calendar', true),
            new Colonne('activite', 'Activité', 'fas fa-cubes', true, true),
            new Colonne('libelle', 'Libellé', 'fas fa-tag', true, true),
            new Colonne('nombre_ticket', 'Nombre de tickets', 'fas ticket-alt', true, true),
            new Colonne('montant', 'Montant', 'fas fa-euro-sign', true, true),
            new Colonne('actions', 'Actions', 'fab fa-telegram-plane')
        ];
        $totaux = $rRecette->getTotauxPourKermesse($kermesse);
        $totaux['montant'] = HFloat::getInstance($totaux['montant'] / 100.0)->getMontantFormatFrancais();
        return $this->render(
            'kermesse/recettes.html.twig',
            [
                'kermesse' => $kermesse,
                'recettes' => $rowGenerator->generateListPourKermesse($kermesse, $order),
                'total' => $totaux,
                'menu' => $this->getMenu($kermesse, static::MENU_RECETTES),
                'colonnes' => $colonnes,
                'order' => $order
            ]
        );
    }

    /**
     * @Route("/kermesses/{id}/remboursements", name="liste_remboursements")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param RemboursementRowGenerator $rGenerator
     * @return Response
     * @throws UnknownEumException
     */
    public function listeRemboursements(Kermesse $kermesse, RemboursementRowGenerator $rGenerator): Response
    {
        return $this->render(
            'kermesse/remboursements.html.twig',
            [
                'kermesse' => $kermesse,
                'rows' => $rGenerator->generateList($kermesse),
                'menu' => $this->getMenu($kermesse, static::MENU_REMBOURSEMENTS)
            ]
        );
    }

    /**
     * @Route("/kermesses/{id}/card", name="carte_kermesse")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param KermesseCardGenerator $cardGenerator
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function afficherCard(Kermesse $kermesse, KermesseCardGenerator $cardGenerator):Response
    {
        return $this->render('kermesse/card.html.twig', [
            'card' => $cardGenerator->generate($kermesse)
        ]);
    }

    /**
     * Génération de l'export comptable pour téléchargement
     * @Route("/kermesses/{id}/export", name="export_comptable")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @return Response
     * @throws BusinessException
     */
    public function genererExportComptable(Kermesse $kermesse):Response
    {
        $response = new BinaryFileResponse($this->business->genererExportComptable($kermesse));
        $response->headers->set('Content-Type', 'text/csv');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "Kermesse{$kermesse->getAnnee()}_ExportComptable.csv"
        );
        return $response;
    }

    /**
     * Affichage du planning des bénévoles
     * @Route("/kermesses/{id}/planning", name="planning_benevoles")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @return Response
     */
    public function showPlanning(Kermesse $kermesse): Response
    {
        $planning = Planning::createFromKermesse($kermesse);
        return $this->render('kermesse/planning.html.twig', [
            'menu' => $this->getMenu($kermesse, static::MENU_PLANNING),
            'planning' => $planning,
            'codeEtablissement' => $kermesse->getEtablissement()->getUsername(),
            'nbCols' => round($planning->getTaillePlage() / 1800)
        ]);
    }

    /**
     * Validation du planning des bénévoles
     * @Route("/kermesses/{id}/planning/valider", name="planning_valider")
     * @Security("kermesse.isProprietaire(user)")
     * @param Kermesse $kermesse
     * @param EmailSender $sender
     * @return Response
     */
    public function validerPlanning(Kermesse $kermesse, EmailSender $sender): Response
    {
        // Regroupement par adresse email
        $creneauxParEmail = [];
        foreach ($kermesse->getActivites() as $activite) {
            foreach ($activite->getCreneaux() as $creneau) {
                $inscriptions = $creneau->getInscriptionBenevoles();
                if ($creneau->getNbBenevolesRecquis() !== $inscriptions->count()) {
                    continue;
                }
                foreach ($inscriptions as $inscription) {
                    if (!$inscription->getValidee()) {
                        continue;
                    }
                    $benevole = $inscription->getBenevole();
                    if (!array_key_exists($benevole->getEmail(), $creneauxParEmail)) {
                        $creneauxParEmail[$benevole->getEmail()] = new RecapitulatifBenevole($benevole);
                    }
                    $creneauxParEmail[$benevole->getEmail()]->addCreneau($creneau);
                }
            }
        }
        // Envoi des emails
        foreach ($creneauxParEmail as $email => $recapBenevole) {
            try {
                $sender
                    ->setTemplate('creneaux_arretes')->setTemplateVars(['racap' => $recapBenevole, 'code' => $kermesse->getEtablissement()->getUsername()])
                    ->envoyer((new ContactDTO())->setTitre("Kermesse - Planification bénévoles")->setDestinataire($email));
            } catch (Exception $exception) {
                $this->logger->error("Erreur lors de l'envoi de l'email à $email", $exception->getTrace());
            }
        }
        $this->addFlash('success', "Les bénévoles des créneaux complets ont été prévenus apr email !");
        return $this->showPlanning($kermesse);
    }
}
