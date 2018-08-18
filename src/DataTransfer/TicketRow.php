<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 14:23
 */

namespace App\DataTransfer;


use App\Entity\Depense;
use App\Entity\Ticket;
use App\Helper\HFloat;
use Doctrine\Common\Collections\ArrayCollection;

class TicketRow
{
    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var int
     */
    private $montant;

    /**
     * @var int
     */
    private $montantAffecte = 0;

    /**
     * @var string
     */
    private $activitesLiees;

    /**
     * TicketRow constructor.
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->montant = $this->ticket->getMontant() ?? 0;
    }

    /**
     * @param int $montant
     * @return $this
     */
    public function setMontantAffecte(int $montant)
    {
        $this->montantAffecte = $montant;
        return $this;
    }

    /**
     * @param string $activites
     */
    public function setActivitesLiees(string $activites)
    {
        $this->activitesLiees = $activites;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->ticket->getDate()->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getNumero(): string
    {
        return $this->ticket->getNumero();
    }

    /**
     * @return string
     */
    public function getAcheteur(): string
    {
        $membre = $this->ticket->getMembre();
        return $membre->getPrenom() . ' ' . $membre->getNom();
    }

    /**
     * @return string
     */
    public function getFournisseur(): string
    {
        return $this->ticket->getFournisseur();
    }

    /**
     * @return string
     */
    public function getMontant(): string
    {
        return HFloat::getInstance($this->montant / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return string
     */
    public function getMontantAffecte(): string
    {
        return HFloat::getInstance($this->montantAffecte / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return string
     */
    public function getActivitesLiees(): string
    {
        return empty($this->activitesLiees) ? 'Aucune activité liée' : $this->activitesLiees;
    }

    /**
     * @return bool
     */
    public function isCompletementAffecte(): bool
    {
        return $this->montant === $this->montantAffecte;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->ticket->getId();
    }
}