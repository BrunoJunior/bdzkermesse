<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 14:23
 */

namespace App\DataTransfer;


use App\Business\TicketBusiness;
use App\Entity\Remboursement;
use App\Enum\RemboursementEtatEnum;
use App\Enum\RemboursementModeEnum;
use App\Helper\HFloat;
use Doctrine\Common\Collections\ArrayCollection;

class RemboursementRow
{
    /**
     * @var Remboursement
     */
    private $remboursement;

    /**
     * @var int
     */
    private $montant;

    /**
     * @var RemboursementEtatEnum
     */
    private $etat;

    /**
     * @var RemboursementModeEnum
     */
    private $mode;

    /**
     * @var ArrayCollection
     */
    private $tickets;

    /**
     * @var TicketBusiness
     */
    private $bTicket;

    /**
     * RemboursementRow constructor.
     * @param Remboursement $remboursement
     * @param TicketBusiness $bTicket
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function __construct(Remboursement $remboursement, TicketBusiness $bTicket)
    {
        $this->bTicket = $bTicket;
        $this->remboursement = $remboursement;
        $this->montant = $this->remboursement->getMontant() ?? 0;
        $this->etat = RemboursementEtatEnum::getInstance($this->remboursement->getEtat());
        if ($this->remboursement->getMode() !== null) {
            $this->mode = RemboursementModeEnum::getInstance($this->remboursement->getMode());
        }
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->remboursement->getDate()->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getNumeroSuivi(): string
    {
        return $this->remboursement->getNumeroSuivi();
    }

    /**
     * @return string
     */
    public function getMembre(): string
    {
        $membre = $this->remboursement->getMembre();
        return $membre->getPrenom() . ' ' . $membre->getNom();
    }

    /**
     * @return string
     */
    public function getMontant(): string
    {
        return HFloat::getInstance($this->montant / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->remboursement->getId();
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
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode ? $this->mode->getLabel() : '';
    }

    /**
     * @return bool
     */
    public function isEnAttente():bool
    {
        return $this->etat->is(RemboursementEtatEnum::EN_ATTENTE);
    }

    /**
     * @return ArrayCollection|TicketRow[]
     * @throws \Doctrine\DBAL\DBALException
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function getTickets():ArrayCollection
    {
        if ($this->tickets === null) {
            $this->tickets = new ArrayCollection();
            foreach ($this->remboursement->getTickets() as $ticket) {
                $this->tickets->add($this->bTicket->getRow($ticket));
            }
        }
        return $this->tickets;
    }
}