<?php

namespace App\Controller;

use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Form\TicketType;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class TicketController extends MyController
{
    /**
     * @param Kermesse $kermesse
     * @return string
     */
    private function getDuplicataDir(Kermesse $kermesse)
    {
        return $this->getParameter('duplicata_dir') . '/' . $kermesse->getId();
    }

    /**
     * @Route("/kermesse/{id}/ticket/new", name="nouveau_ticket")
     * @Security("kermesse.isProprietaire(user)")
     */
    public function nouveauTicket(Request $request, Kermesse $kermesse, FileUploader $uploader)
    {
        $ticket = new Ticket();
        $ticket->setKermesse($kermesse);
        $form = $this->createForm(TicketType::class, $ticket, ['kermesse' => $kermesse]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $ticket->getDuplicata();
            if ($file) {
                $filename = $uploader->upload($file, $this->getDuplicataDir($kermesse));
                $ticket->setDuplicata($filename);
            }
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
     * @Security("ticket.isProprietaire(user)")
     */
    public function editerTicket(Request $request, Ticket $ticket, FileUploader $uploader)
    {
        $kermesse = $ticket->getKermesse();
        $prevDuplicata = null;
        if ($ticket->getDuplicata()) {
            $prevDuplicata = $ticket->getDuplicata();
            $ticket->setDuplicata(new File($this->getDuplicataDir($kermesse) . '/' . $prevDuplicata));
        }
        $form = $this->createForm(TicketType::class, $ticket, ['kermesse' => $kermesse]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $ticket->getDuplicata();
            if ($file) {
                $filename = $uploader->upload($file, $this->getDuplicataDir($kermesse));
                $ticket->setDuplicata($filename);
                if ($prevDuplicata) {
                    unlink($this->getDuplicataDir($kermesse) . '/' . $prevDuplicata);
                }
            } elseif ($prevDuplicata) {
                $ticket->setDuplicata($prevDuplicata);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();
            return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'ticket/edition.html.twig',
            [
                'form' => $form->createView(),
                'menu' => $this->getMenu($kermesse, static::MENU_TICKETS)
            ]
        );
    }

    /**
     * @Route("/ticket/{id}/supprimer", name="supprimer_ticket")
     * @Security("ticket.isProprietaire(user)")
     */
    public function supprimerTicket(Ticket $ticket)
    {
        $kermesse = $ticket->getKermesse();
        if ($ticket->getDuplicata()) {
            unlink($this->getDuplicataDir($kermesse) . '/' . $ticket->getDuplicata());
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($ticket);
        $em->flush();
        return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
    }
}
