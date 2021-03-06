<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 14:23
 */

namespace App\DataTransfer;

use App\Entity\Ticket;
use App\Enum\TicketEtatEnum;
use App\Helper\HFloat;
use App\Service\DuplicataDirectoryGenerator;
use SimpleEnum\Exception\UnknownEumException;

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
     * @var array
     */
    private $activitesLiees;

    /**
     * @var TicketEtatEnum
     */
    private $etat;

    /**
     * @var DuplicataDirectoryGenerator
     */
    private $duplicataDirGen;

    /**
     * TicketRow constructor.
     * @param Ticket $ticket
     * @param DuplicataDirectoryGenerator $duplicataDirGen
     * @throws UnknownEumException
     */
    public function __construct(Ticket $ticket, DuplicataDirectoryGenerator $duplicataDirGen)
    {
        $this->ticket = $ticket;
        $this->montant = $this->ticket->getMontant() ?? 0;
        $this->etat = TicketEtatEnum::getInstance($this->ticket->getEtat());
        $this->duplicataDirGen = $duplicataDirGen;
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
     * @param array $activites
     */
    public function setActivitesLiees(array $activites)
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
        return empty($this->activitesLiees) ? 'Aucune activité liée' : implode(', ', $this->activitesLiees);
    }

    /**
     * @return array
     */
    public function getActivites(): array
    {
        return $this->activitesLiees ?? [];
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

    /**
     * @return int
     */
    public function getIdAcheteur(): int
    {
        return $this->ticket->getMembre()->getId();
    }

    /**
     * @return null|string
     */
    public function getCheminFichier(): ?string
    {
        $duplicata = $this->ticket->getDuplicata();
        if ($duplicata) {
            return $this->duplicataDirGen->getDuplicataPath($this->ticket);
        }
        return null;
    }

    /**
     * @return string
     */
    public function getEtat(): string
    {
        return $this->etat->getLabel();
    }

    /**
     * @return string
     */
    public function getPastilleEtat(): string
    {
        return $this->etat->getPastille();
    }

    /**
     * Le commentaire
     * @return string
     */
    public function getCommentaire(): string
    {
        return $this->ticket->getCommentaire() ?? '';
    }

    /**
     * Entité liée
     * @return Ticket
     */
    public function getEntity(): Ticket
    {
        return $this->ticket;
    }
}
