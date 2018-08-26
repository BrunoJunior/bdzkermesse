<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 16:21
 */

namespace App\Business;


use App\DataTransfer\RemboursementDTO;
use App\DataTransfer\ContactDTO;
use App\DataTransfer\RemboursementRow;
use App\Entity\Kermesse;
use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Enum\RemboursementEtatEnum;
use App\Exception\BusinessException;
use App\Service\EmailSender;
use Doctrine\ORM\EntityManagerInterface;

class RemboursementBusiness
{
    /**
     * @var MembreBusiness
     */
    private $bMembre;
    /**
     * @var TicketBusiness
     */
    private $bTicket;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var EmailSender
     */
    private $sender;

    /**
     * RemboursementBusiness constructor.
     * @param TicketBusiness $bTicket
     * @param MembreBusiness $bMembre
     * @param EntityManagerInterface $entityManager
     * @param EmailSender $sender
     */
    public function __construct(TicketBusiness $bTicket, MembreBusiness $bMembre, EntityManagerInterface $entityManager, EmailSender $sender)
    {
        $this->bMembre = $bMembre;
        $this->bTicket = $bTicket;
        $this->em = $entityManager;
        $this->sender = $sender;
    }

    /**
     * @param Remboursement $remboursement
     * @param Membre $membre
     * @return Remboursement
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function initialiserDemandeRemboursement(Remboursement $remboursement, Membre $membre):Remboursement
    {
        $remboursement->setMembre($membre);
        $remboursement->setEtat(RemboursementEtatEnum::EN_ATTENTE);
        $remboursement->setDate(new \DateTime());
        $remboursement->setNumeroSuivi($this->bMembre->getProchainNumeroSuivi($membre));
        return $remboursement;
    }

    /**
     * Création de la demande en BDD
     * @param Remboursement $remboursement
     * @throws BusinessException
     * @throws \SimpleEnum\Exception\UnknownEumException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function creerDemande(Remboursement $remboursement)
    {
        $montant = 0;
        foreach ($remboursement->getTickets() as $ticket) {
            $montant += $ticket->getMontant();
            $this->bTicket->synchroniserEtats($ticket, $remboursement);
        }
        $remboursement->setMontant($montant);
        $this->em->persist($remboursement);
        $this->em->flush();
        $this->envoyerMailDemande($remboursement);
    }

    /**
     * Validation d'un remboursement
     * @param Remboursement $remboursement
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function valider(Remboursement $remboursement)
    {
        $remboursement->setEtat(RemboursementEtatEnum::VALIDE);
        foreach ($remboursement->getTickets() as $ticket) {
            $this->bTicket->synchroniserEtats($ticket, $remboursement);
        }
        $this->em->persist($remboursement);
        $this->em->flush();
    }

    /**
     * @param Remboursement $remboursement
     * @throws BusinessException
     * @throws \SimpleEnum\Exception\UnknownEumException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function envoyerMailDemande(Remboursement $remboursement)
    {
        if ($remboursement->getEtat() === RemboursementEtatEnum::VALIDE) {
            throw new BusinessException("Le remboursement a déjà été validé !");
        }
        $contact = $this->bMembre
            ->initialiserContact($remboursement->getMembre(), new ContactDTO())
            ->setTitre("BdzKermesse - Demande de remboursement")
            ->setEmetteur('bdzkermesse@bdesprez.com');
        $retour = $this->sender
            ->setTemplate('remboursement_demande')
            ->setTemplateVars(['demande' => new RemboursementRow($remboursement, $this->bTicket)])
            ->envoyer($contact, function (\Swift_Message $message) use ($remboursement) {
                foreach ($remboursement->getTickets() as $ticket) {
                    if ($ticket->getDuplicata()) {
                        $filepath = $this->bTicket->getDuplicataPath($ticket);
                        $message->attach(\Swift_Attachment::fromPath($filepath)->setFilename($ticket->getNumero() . '.' . pathinfo($filepath,PATHINFO_EXTENSION)));
                    }
                }
            });
        if ($retour < 1) {
            throw new BusinessException("Erreur lors de l'envoi du message !");
        }
    }

    /**
     * @param Remboursement $remboursement
     * @return Kermesse|null
     */
    public function getKermesse(Remboursement $remboursement):?Kermesse
    {
        $tickets = $remboursement->getTickets();
        if ($tickets->isEmpty()) {
            return null;
        }
        return $tickets->first()->getKermesse();
    }
}