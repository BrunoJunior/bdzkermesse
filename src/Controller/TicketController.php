<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Form\TicketType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TicketController extends Controller
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
            return $this->redirectToRoute('kermesse', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'ticket/nouveau.html.twig',
            array('form' => $form->createView())
        );
    }
}
