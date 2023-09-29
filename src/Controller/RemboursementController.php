<?php

namespace App\Controller;

use App\Business\RemboursementBusiness;
use App\Business\TicketBusiness;
use App\DataTransfer\Colonne;
use App\DataTransfer\RemboursementRow;
use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Exception\BusinessException;
use App\Form\DemandeRemboursementType;
use App\Form\ValiderRemboursementType;
use App\Repository\TicketRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Psr\Log\LoggerInterface;
use SimpleEnum\Exception\UnknownEumException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
     * @param Request $request
     * @param Remboursement $remboursement
     * @param TicketBusiness $bTicket
     * @return Response
     * @throws UnknownEumException
     */
    public function details(Request $request, Remboursement $remboursement, TicketBusiness $bTicket): Response
    {
        $order = $request->get('order', 'date');
        $colonnes = [
            new Colonne('id', '#', '', true),
            new Colonne('etat', 'État', 'fas fa-question-circle', true),
            new Colonne('date', 'Date', 'fas fa-calendar', true),
            new Colonne('membre', 'Acheteur', 'fas fa-user', true, true),
            new Colonne('numero', 'N°', 'fas fa-barcode', true, true),
            new Colonne('fournisseur', 'Fournisseur', 'fas fa-truck', true, true),
            new Colonne('montant', 'Montant', 'fas fa-receipt', true, true),
            new Colonne('activites', 'Activités liées', 'fas fa-link'),
            new Colonne('actions', 'Actions', 'fab fa-telegram-plane'),
        ];
        return $this->render(
            'remboursement/index.html.twig',
            [
                'remboursement' => new RemboursementRow($remboursement, $bTicket),
                'menu' => $this->getMenu($this->business->getKermesse($remboursement), static::MENU_REMBOURSEMENTS),
                'colonnes' => $colonnes,
                'order' => $order
            ]
        );
    }

    /**
     * @Route("/membres/{id}/remboursements/demande", name="demande_remboursement")
     * @Security("membre.isProprietaire(user)")
     * @param Request $request
     * @param Membre $membre
     * @param TicketRepository $rTicket
     * @return RedirectResponse|Response
     * @throws BusinessException
     * @throws LoaderError
     * @throws NonUniqueResultException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws UnknownEumException
     */
    public function demanderRemboursement(Request $request, Membre $membre, TicketRepository $rTicket): Response
    {
        $ticketsNonRembourses = $rTicket->findNonRembourses($membre);
        $remboursement = $this->business->initialiserDemandeRemboursement(new Remboursement(), $membre);
        $form = $this->createForm(DemandeRemboursementType::class, $remboursement, [
            'tickets' => $ticketsNonRembourses,
            'action' => $this->generateUrl('demande_remboursement', ['id' => $membre->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->creerDemande($remboursement);
            return $this->reponseModal("Demande de remboursement effectuée avec succès !<br />Un e-mail vous a été envoyé !");
        }
        return $this->render('remboursement/form_demande.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/remboursements/{id}/valider", name="valider_remboursement")
     * @Security("remboursement.isProprietaire(user)")
     * @param Request $request
     * @param Remboursement $remboursement
     * @return Response
     * @throws Exception
     */
    public function validerRemboursement(Request $request, Remboursement $remboursement): Response
    {
        $remboursement->setDate(new DateTime());
        $form = $this->createForm(ValiderRemboursementType::class, $remboursement, [
            'action' => $this->generateUrl('valider_remboursement', ['id' => $remboursement->getId()])
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->business->valider($remboursement);
            return $this->reponseModal("Le remboursement est considéré comme valide !");
        }
        return $this->render('remboursement/form_valider.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/remboursements/{id}/renvoyer_demande", name="renvoyer_demande")
     * @Security("remboursement.isProprietaire(user)")
     * @param Remboursement $remboursement
     * @return Response
     * @throws UnknownEumException
     * @throws BusinessException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renvoyerEmailDemande(Remboursement $remboursement):Response
    {
        $this->business->envoyerMailDemande($remboursement);
        return $this->reponseModal("La demande de remboursement vous a été renvoyée par email !");
    }
}
