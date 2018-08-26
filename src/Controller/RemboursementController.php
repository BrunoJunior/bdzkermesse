<?php

namespace App\Controller;

use App\Business\RemboursementBusiness;
use App\Business\TicketBusiness;
use App\DataTransfer\RemboursementDTO;
use App\DataTransfer\RemboursementRow;
use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Form\DemandeRemboursementType;
use App\Form\ValiderRemboursementType;
use App\Repository\TicketRepository;
use Psr\Log\LoggerInterface;
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
     * @param LoggerInterface $logger
     */
    public function __construct(RemboursementBusiness $business, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->business = $business;
    }

    /**
     * @Route("/remboursements/{id}", name="remboursement", requirements={"id"="\d+"})
     * @Security("remboursement.isProprietaire(user)")
     * @param Remboursement $remboursement
     * @param TicketBusiness $bTicket
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function details(Remboursement $remboursement, TicketBusiness $bTicket): Response
    {
        return $this->render(
            'remboursement/index.html.twig',
            [
                'remboursement' => new RemboursementRow($remboursement, $bTicket),
                'menu' => $this->getMenu($this->business->getKermesse($remboursement), static::MENU_REMBOURSEMENTS)
            ]
        );
    }

    /**
     * @Route("/membres/{id}/remboursements/demande", name="demande_remboursement")
     * @Security("membre.isProprietaire(user)")
     * @param Request $request
     * @param Membre $membre
     * @param TicketRepository $rTicket
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function demanderRemboursement(Request $request, Membre $membre, TicketRepository $rTicket): Response
    {
        $ticketsNonRembourses = $rTicket->findNonRembourses($membre);
        $remboursement = $this->business->initialiserDemandeRemboursement(new Remboursement(), $membre);
        $form = $this->createForm(DemandeRemboursementType::class, $remboursement, ['tickets' => $ticketsNonRembourses]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->business->creerDemande($remboursement);
                $this->addFlash("success", "Demande de remboursement effectuée avec succès !");
                $this->addFlash("success", "Un e-mail vous a été envoyé !");
                return $this->redirectToRoute('membres');
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getTraceAsString());
                $this->addFlash("danger", "Une erreur s'est produite lors de la demande de remboursement !");
            }
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
     */
    public function validerRemboursement(Request $request, Remboursement $remboursement): Response
    {
        $remboursement->setDate(new \DateTime());
        $form = $this->createForm(ValiderRemboursementType::class, $remboursement);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->business->valider($remboursement);
                $this->addFlash("success", "Le remboursement est considéré comme valide !");
                return $this->redirectToRoute('membres');
            } catch (\Exception $exception) {
                $this->addFlash("danger", "Une erreur s'est produite durant la validation !");
                $this->logger->critical($exception);
            }
        }
        return $this->render('remboursement/form_valider.html.twig', [
            'form' => $form->createView(),
            'menu' => $this->getMenu(null, static::MENU_MEMBRES)
        ]);
    }

    /**
     * @Route("/remboursements/{id}/renvoyer_demande", name="renvoyer_demande")
     * @Security("remboursement.isProprietaire(user)")
     * @param Remboursement $remboursement
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renvoyerEmailDemande(Remboursement $remboursement):Response
    {
        try {
            $this->business->envoyerMailDemande($remboursement);
            $this->addFlash("success", "La demande de remboursement vous a été renvoyée par email !");
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getTraceAsString());
            $this->addFlash("danger", $exception->getMessage());
        }
        return $this->redirectToRoute('membres');
    }
}
