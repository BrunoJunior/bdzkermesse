<?php

namespace App\Controller;

use App\Business\TicketBusiness;
use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Form\TicketType;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/kermesses/{id}/ticket/new", name="nouveau_ticket")
     * @Security("kermesse.isProprietaire(user)")
     * @param Request $request
     * @param Kermesse $kermesse
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \SimpleEnum\Exception\UnknownEumException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function nouveauTicket(Request $request, Kermesse $kermesse)
    {
        $ticket = new Ticket();
        $ticket->setKermesse($kermesse);
        $form = $this->createForm(TicketType::class, $ticket, ['kermesse' => $kermesse]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->creer($ticket);
            $this->addFlash("success", "Ticket enregistré avec succès !");
            return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'ticket/nouveau.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, static::MENU_TICKETS)
            ]
        );
    }

    /**
     * @Route("/tickets/{id}/edit", name="editer_ticket")
     * @Security("ticket.isProprietaire(user)")
     * @param Request $request
     * @param Ticket $ticket
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editerTicket(Request $request, Ticket $ticket)
    {
        $kermesse = $ticket->getKermesse();
        $prevDuplicata = $ticket->getDuplicata();
        $form = $this->createForm(TicketType::class, $ticket, ['kermesse' => $kermesse]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->modifier($ticket, $prevDuplicata);
            $this->addFlash("success", "Ticket enregistré avec succès !");
            return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'ticket/edition.html.twig',
            [
                'form' => $form->createView(),
                'duplicata' => $kermesse->getId() . '/' . $prevDuplicata,
                'is_image' => $this->business->isDuplicataImage($ticket),
                'menu' => $this->getMenu($kermesse, static::MENU_TICKETS)
            ]
        );
    }

    /**
     * @Route("/tickets/{id}/supprimer", name="supprimer_ticket")
     * @Security("ticket.isProprietaire(user)")
     * @param Ticket $ticket
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function supprimerTicket(Ticket $ticket)
    {
        $this->business->supprimer($ticket);
        $this->addFlash("success", "Ticket supprimé !");
        return $this->redirectToRoute('liste_tickets', ['id' => $ticket->getKermesse()->getId()]);
    }
}
