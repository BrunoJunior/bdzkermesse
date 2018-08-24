<?php

namespace App\Controller;

use App\Business\RemboursementBusiness;
use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Form\DemandeRemboursementType;
use App\Form\ValiderRemboursementType;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class RemboursementController extends MyController
{
    /**
     * @var RemboursementBusiness
     */
    private $business;

    /**
     * RemboursementController constructor.
     * @param RemboursementBusiness $business
     */
    public function __construct(RemboursementBusiness $business)
    {
        $this->business = $business;
    }

    /**
     * @Route("/membres/{id}/remboursements/demande", name="demande_remboursement")
     * @Security("membre.isProprietaire(user)")
     * @param Request $request
     * @param Membre $membre
     * @param TicketRepository $rTicket
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function demanderRemboursement(Request $request, Membre $membre, TicketRepository $rTicket): Response
    {
        $ticketsNonRembourses = $rTicket->findNonRembourses($membre);
        $remboursement = $this->business->initialiserDemandeRemboursement(new Remboursement());
        $form = $this->createForm(DemandeRemboursementType::class, $remboursement, ['tickets' => $ticketsNonRembourses]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->creerDemande($remboursement);
            $this->addFlash("success", "Demande de remboursement effectuée avec succès !");
            $this->addFlash("success", "Un e-mail vous a été envoyé !");
            return $this->redirectToRoute('membres');
        }
        return $this->render('remboursement/form_demande.html.twig', [
            'form' => $form->createView(),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }

    /**
     * @Route("/remboursements/{id}/valider", name="valider_remboursement")
     * @Security("remboursement.isProprietaire(user)")
     * @param Request $request
     * @param Remboursement $remboursement
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function validerRemboursement(Request $request, Remboursement $remboursement): Response
    {
        $remboursement->setDate(new \DateTime());
        $form = $this->createForm(ValiderRemboursementType::class, $remboursement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->valider($remboursement);
            $this->addFlash("success", "Le remboursement est considéré comme valide !");
            return $this->redirectToRoute('membres');
        }
        return $this->render('remboursement/form_valider.html.twig', [
            'form' => $form->createView(),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }
}
