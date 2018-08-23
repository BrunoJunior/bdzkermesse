<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 14:23
 */

namespace App\DataTransfer;


use App\Entity\Depense;
use App\Entity\Remboursement;
use App\Entity\Ticket;
use App\Enum\RemboursementEtatEnum;
use App\Enum\RemboursementModeEnum;
use App\Enum\TicketEtatEnum;
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
     * RemboursementRow constructor.
     * @param Remboursement $remboursement
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function __construct(Remboursement $remboursement)
    {
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
}