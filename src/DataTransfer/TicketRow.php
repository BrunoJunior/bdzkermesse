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
     * @var ArrayCollection
     */
    private $activitesLies;

    /**
     * TicketRow constructor.
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->montant = $this->ticket->getMontant() ?? 0;
        $this->activitesLies = new ArrayCollection();
        foreach ($ticket->getDepenses() as $depense) {
            $this->montantAffecte += $depense->getMontant();
            $this->activitesLies->add($depense->getActivite()->getNom());
        }
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
        return $this->activitesLies->isEmpty() ? 'Aucune activité liée' : implode(', ', $this->activitesLies->toArray());
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