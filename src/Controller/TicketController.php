<?php

namespace App\Controller;

use App\Business\TicketBusiness;
use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Form\TicketType;
use App\Service\FileUploader;
use Stringy\Stringy;
use Symfony\Component\HttpFoundation\File\File;
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
     */
    public function __construct(TicketBusiness $business)
    {
        $this->business = $business;
    }

    /**
     * @Route("/kermesses/{id}/ticket/new", name="nouveau_ticket")
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
                $filename = $uploader->upload($file, $this->business->getDuplicataDir($kermesse));
                $ticket->setDuplicata($filename);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();
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
     */
    public function editerTicket(Request $request, Ticket $ticket, FileUploader $uploader)
    {
        $kermesse = $ticket->getKermesse();
        $prevDuplicata = null;
        $file = null;
        if ($ticket->getDuplicata()) {
            $prevDuplicata = $ticket->getDuplicata();
            $file = new File($this->business->getDuplicataDir($kermesse) . '/' . $prevDuplicata);
            $ticket->setDuplicata($file);
        }
        $form = $this->createForm(TicketType::class, $ticket, ['kermesse' => $kermesse]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $ticket->getDuplicata();
            if ($file) {
                $filename = $uploader->upload($file, $this->business->getDuplicataDir($kermesse));
                $ticket->setDuplicata($filename);
                if ($prevDuplicata) {
                    unlink($this->business->getDuplicataDir($kermesse) . '/' . $prevDuplicata);
                }
            } elseif ($prevDuplicata) {
                $ticket->setDuplicata($prevDuplicata);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($ticket);
            $em->flush();
            $this->addFlash("success", "Ticket enregistré avec succès !");
            return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
        }
        return $this->render(
            'ticket/edition.html.twig',
            [
                'form' => $form->createView(),
                'duplicata' => $kermesse->getId() . '/' . $prevDuplicata,
                'is_image' => $file ? Stringy::create($file->getMimeType())->startsWith('image/') : false,
                'menu' => $this->getMenu($kermesse, static::MENU_TICKETS)
            ]
        );
    }

    /**
     * @Route("/tickets/{id}/supprimer", name="supprimer_ticket")
     * @Security("ticket.isProprietaire(user)")
     */
    public function supprimerTicket(Ticket $ticket)
    {
        $kermesse = $ticket->getKermesse();
        $this->business->supprimer($ticket);
        $this->addFlash("success", "Ticket supprimé !");
        return $this->redirectToRoute('liste_tickets', ['id' => $kermesse->getId()]);
    }
}
