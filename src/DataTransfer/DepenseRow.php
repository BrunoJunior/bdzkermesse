<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 14:23
 */

namespace App\DataTransfer;


use App\Entity\Depense;
use App\Entity\Kermesse;
use App\Helper\HFloat;

class DepenseRow
{
    /**
     * @var Depense
     */
    private $depense;

    /**
     * DepenseRow constructor.
     * @param Depense $depense
     */
    public function __construct(Depense $depense)
    {
        $this->depense = $depense;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->depense->getTicket()->getDate()->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getNumero(): string
    {
        return $this->depense->getTicket()->getNumero();
    }

    /**
     * @return string
     */
    public function getAcheteur(): string
    {
        $membre = $this->depense->getTicket()->getMembre();
        return $membre->getPrenom() . ' ' . $membre->getNom();
    }

    /**
     * @return string
     */
    public function getFournisseur(): string
    {
        return $this->depense->getTicket()->getFournisseur();
    }

    /**
     * @return string
     */
    public function getMontant(): string
    {
        return HFloat::getInstance($this->depense->getMontant() / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->depense->getId();
    }

    /**
     * @return int
     */
    public function getIdTicket(): int
    {
        return $this->depense->getTicket()->getId();
    }

    /**
     * @return bool
     */
    public function isForKermesse(): bool
    {
        return $this->depense->getTicket()->getKermesse() instanceof Kermesse;
    }

    /**
     * @return bool
     */
    public function hasDuplicata(): bool {
        $ticket = $this->depense->getTicket();
        return $ticket && $ticket->getDuplicata() !== null;
    }
}
