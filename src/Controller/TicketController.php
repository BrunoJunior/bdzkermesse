<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Form\TicketType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends MyController
{

    /**
     * @Route("/kermesse/{id}/ticket/new", name="nouveau_ticket")
     */
    public function nouveauTicket(Request $request, Kermesse $kermesse)
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket, ['kermesse' => $kermesse]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setKermesse($kermesse);
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();
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
     * @Route("/ticket/{id}/edit", name="editer_ticket")
     */
    public function editerTicket(Request $request, Ticket $ticket)
    {
        $form = $this->createForm(TicketType::class, $ticket, ['kermesse' => $ticket->getKermesse()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();
            return $this->redirectToRoute('liste_tickets', ['id' => $ticket->getKermesse()->getId()]);
        }
        return $this->render(
            'ticket/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($ticket->getKermesse(), static::MENU_TICKETS)
            ]
        );
    }

    /**
     * @Route("/ticket/{id}/supprimer", name="supprimer_ticket")
     */
    public function supprimerTicket(Ticket $ticket)
    {
        $kermesse = $ticket->getKermesse();
        $em = $this->getDoctrine()->getManager();
        $em->remove($ticket);
        $em->flush();
        return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
    }
}
