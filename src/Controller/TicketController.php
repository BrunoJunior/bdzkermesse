<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Form\TicketType;
use App\Helper\Breadcrumb;
use App\Helper\MenuLink;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends MyController
{

    /**
     * @param Kermesse $kermesse
     * @return Breadcrumb
     */
    private function getMenu(Kermesse $kermesse) {
        return Breadcrumb::getInstance(false)
            ->addLink(MenuLink::getInstance('Accueil', 'home', $this->generateUrl('index')))
            ->addLink($this->getKermessesMenuLink($kermesse))
            ->addLink(MenuLink::getInstance('Membres', 'users', $this->generateUrl('membres')));
    }

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
            return $this->redirectToRoute('index');
        }
        return $this->render(
            'ticket/nouveau.html.twig',
            [
                'form' => $form->createView(),
                'kermesse' => $kermesse,
                'menu' => $this->getMenu($kermesse)
            ]
        );
    }
}
