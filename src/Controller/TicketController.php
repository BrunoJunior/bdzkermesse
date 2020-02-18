<?php

namespace App\Controller;

use App\Business\TicketBusiness;
use App\Entity\Activite;
use App\Entity\Depense;
use App\Entity\Etablissement;
use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Exception\BusinessException;
use App\Form\TicketType;
use App\Helper\Breadcrumb;
use App\Repository\ActiviteRepository;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class TicketController extends MyController
{
    /**
     * @var TicketBusiness
     */
    private $business;

    /**
     * TicketController constructor.
     * @param TicketBusiness $business
     * @param LoggerInterface $logger
     */
    public function __construct(TicketBusiness $business, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->business = $business;
    }

    /**
     * @param Kermesse|null $kermesse
     * @return Response
     */
    private function redirectVersTicketsOuActions(?Kermesse $kermesse): Response
    {
        if ($kermesse !== null) {
            return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
        }
        return  $this->redirectToRoute('lister_actions');
    }

    /**
     * @param Kermesse|null $kermesse
     * @return Breadcrumb
     */
    private function getMenuSuivantKermesse(?Kermesse $kermesse): Breadcrumb
    {
        if ($kermesse) {
            return $this->getMenu($kermesse, static::MENU_TICKETS);
        }
        return $this->getMenu($kermesse, static::MENU_ACTIVITES_AUTRES);
    }

    /**
     * @param Request $request
     * @param ActiviteRepository $rActivite
     * @param Kermesse|null $kermesse
     * @param Activite|null $activite
     * @return Response
     * @throws Exception
     */
    private function newTicket(Request $request, ActiviteRepository $rActivite, ?Kermesse $kermesse, ?Activite $activite = null): Response
    {
        $etablissement = $this->getUser();
        if (!$etablissement instanceof Etablissement) {
            throw new NotFoundHttpException("La page demandée n'existe pas !");
        }
        $ticket = new Ticket();
        $ticket->setKermesse($kermesse);
        $ticket->setEtablissement($etablissement);
        if ($activite && count($ticket->getDepenses()) === 0) {
            $ticket->addDepense((new Depense())
                ->setEtablissement($etablissement)
                ->setActivite($activite)
                ->setMontant($ticket->getMontant() ?: 0));
        }
        $form = $this->createForm(TicketType::class, $ticket, [
            'kermesse' => $kermesse,
            'etablissement' => $etablissement,
            'actions' => $rActivite->getListeAutres($etablissement),
            'activite' => $activite
        ]);
        $form->handleRequest($request);
        // Une seule activité, montant dépense = montant ticket
        if ($activite) {
            $ticket->getDepenses()[0]->setMontant($ticket->getMontant() ?: 0);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->creer($ticket);
            $this->addFlash("success", "Ticket enregistré avec succès !");
            return $this->redirectVersTicketsOuActions($kermesse);
        }
        return $this->render(
            'ticket/nouveau.html.twig',
            [
                'form' => $form->createView(),
                'activite' => $activite,
                'menu' => $this->getMenuSuivantKermesse($kermesse),
            ]
        );
    }

    /**
     * @Route("/kermesses/{id<\d+>}/ticket/new", name="nouveau_ticket")
     * @Security("kermesse.isProprietaire(user)")
     * @param Request $request
     * @param Kermesse|null $kermesse
     * @param ActiviteRepository $rActivite
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function nouveauTicket(Request $request, ?Kermesse $kermesse, ActiviteRepository $rActivite)
    {
        return $this->newTicket($request, $rActivite, $kermesse);
    }

    /**
     * @Route("/actions/{id<\d+>}/ticket/new", name="nouveau_ticket_action")
     * @param Activite $activite
     * @param Request $request
     * @param ActiviteRepository $rActivite
     * @return RedirectResponse|Response
     * @throws Exception
     */
    public function nouveauTicketActivite(Activite $activite, Request $request, ActiviteRepository $rActivite)
    {
        return $this->newTicket($request, $rActivite, $activite->getKermesse(), $activite);
    }

    /**
     * @Route("/tickets/{id<\d+>}/edit", name="editer_ticket")
     * @Security("ticket.isProprietaire(user)")
     * @param Request $request
     * @param Ticket $ticket
     * @param ActiviteRepository $rActivite
     * @return Response
     * @throws Exception
     */
    public function editerTicket(Request $request, Ticket $ticket, ActiviteRepository $rActivite): Response
    {
        $kermesse = $ticket->getKermesse();
        $prevDuplicata = $ticket->getDuplicata();
        if ($prevDuplicata) {
            try {
                $ticket->setDuplicata(new File($this->business->getDuplicataPath($ticket)));
            } catch (FileNotFoundException $exc) {
                $this->business->supprimerDuplicata($ticket);
            }
        }
        $form = $this->createForm(TicketType::class, $ticket, [
            'kermesse' => $kermesse,
            'etablissement' => $this->getUser(),
            'actions' => $rActivite->getListeAutres($ticket->getEtablissement())
        ]);
        $form->handleRequest($request);
        $activite = null;
        if ($kermesse === null) {
            $depenses = $ticket->getDepenses();
            $activite = count($depenses) === 1 ? $depenses[0]->getActivite() : null;
            $depenses[0]->setMontant($ticket->getMontant() ?: 0);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->modifier($ticket, $prevDuplicata);
            $this->addFlash("success", "Ticket enregistré avec succès !");
            return $this->redirectVersTicketsOuActions($kermesse);
        }
        return $this->render(
            'ticket/edition.html.twig',
            [
                'id' => $ticket->getId(),
                'form' => $form->createView(),
                'duplicata' => $kermesse->getId() . '/' . $prevDuplicata,
                'is_image' => $this->business->isDuplicataImage($ticket),
                'activite' => $activite,
                'menu' => $this->getMenuSuivantKermesse($kermesse),
            ]
        );
    }

    /**
     * @Route("/tickets/{id<\d+>}/duplicata", name="ticket_duplicata", methods={"GET"})
     * @Security("ticket.isProprietaire(user)")
     * @param Ticket $ticket
     * @param TicketBusiness $ticketBusiness
     * @return Response
     */
    public function afficherDuplicata(Ticket $ticket, TicketBusiness $ticketBusiness): Response
    {
        if (!$ticket->getDuplicata()) {
            throw new NotFoundHttpException("Aucun duplicata pour ce ticket");
        }
        $duplicata = $ticketBusiness->getDuplicataPath($ticket);
        $response = new BinaryFileResponse($duplicata);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            "Ticket_{$ticket->getNumero()}.{$response->getFile()->getExtension()}"
        );
        return $response;
    }

    /**
     * @Route("/tickets/{id<\d+>}/supprimer", name="supprimer_ticket")
     * @Security("ticket.isProprietaire(user)")
     * @param Ticket $ticket
     * @return Response
     */
    public function supprimerTicket(Ticket $ticket): Response
    {
        try {
            $this->business->supprimer($ticket);
            $this->addFlash("success", "Ticket supprimé !");
        } catch (BusinessException $exc) {
            $this->addFlash("danger", $exc->getMessage());
        }

        return $this->redirectVersTicketsOuActions($ticket->getKermesse());
    }

    /**
     * @Route("/tickets/{id<\d+>}/supprimer_duplicata", name="supprimer_duplicata")
     * @Security("ticket.isProprietaire(user)")
     * @param Ticket $ticket
     * @return Response
     */
    public function supprimerDuplicata(Ticket $ticket): Response
    {
        $this->business->supprimerDuplicata($ticket);
        $this->addFlash("success", "Duplicata supprimé !");
        return $this->redirectToRoute('editer_ticket', ['id' => $ticket->getId()]);
    }
}
