<?php

namespace App\Controller;

use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Enum\RemboursementEtatEnum;
use App\Enum\TicketEtatEnum;
use App\Form\DemandeRemboursementType;
use App\Repository\RemboursementRepository;
use App\Repository\TicketRepository;
use Stringy\Stringy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class RemboursementController extends MyController
{
    /**
     * @Route("/membre/{id}/remboursement/demande", name="demande_remboursement")
     * @Security("membre.isProprietaire(user)")
     * @param Request $request
     * @param Membre $membre
     * @param TicketRepository $rTicket
     * @param RemboursementRepository $rRemboursement
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function demanderRemboursement(Request $request, Membre $membre, TicketRepository $rTicket, RemboursementRepository $rRemboursement)
    {
        $ticketsNonRembourses = $rTicket->findNonRembourses($membre);
        $remboursement = new Remboursement();
        $remboursement->setMembre($membre);
        $remboursement->setEtat(RemboursementEtatEnum::EN_ATTENTE);
        $remboursement->setDate(new \DateTime());
        $remboursement->setNumeroSuivi(Stringy::create($membre->getPrenom())->first(1)->toUpperCase() . Stringy::create($membre->getNom())->first(1)->toUpperCase() . ($rRemboursement->countRemboursementsMembres($membre) + 1));
        $form = $this->createForm(DemandeRemboursementType::class, $remboursement, ['tickets' => $ticketsNonRembourses]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $montant = 0;
            foreach ($remboursement->getTickets() as $ticket) {
                $montant += $ticket->getMontant();
                $ticket->setEtat(TicketEtatEnum::EN_ATTENTE);
                $em->persist($ticket);
            }
            $remboursement->setMontant($montant);
            $em->persist($remboursement);
            $em->flush();
            return $this->redirectToRoute('membres');
        }
        return $this->render('remboursement/form_demande.html.twig', [
            'form' => $form->createView(),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }
}
