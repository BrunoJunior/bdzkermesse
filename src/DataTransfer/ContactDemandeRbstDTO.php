<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 15:48
 */

namespace App\DataTransfer;

use App\Entity\Remboursement;
use App\Helper\HFloat;
use Doctrine\Common\Collections\ArrayCollection;

class ContactDemandeRbstDTO
{
    /**
     * @var Remboursement
     */
    private $remboursement;

    /**
     * @var ArrayCollection
     */
    private $tickets;

    /**
     * @param Remboursement $remboursement
     * @return ContactDemandeRbstDTO
     */
    public function setRemboursement(Remboursement $remboursement): self
    {
        $this->remboursement = $remboursement;
        return $this;
    }

    /**
     * @return string
     */
    public function getMontant():string
    {
        return HFloat::getInstance($this->remboursement->getMontant()/100.0)->getMontantFormatFrancais();
    }

    /**
     * @return string
     */
    public function getNumeroSuivi():string
    {
        return $this->remboursement->getNumeroSuivi();
    }

    /**
     * @return ArrayCollection|TicketRow[]
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function getTickets():ArrayCollection
    {
        if ($this->tickets === null) {
            $this->tickets = new ArrayCollection();
            foreach ($this->remboursement->getTickets() as $ticket) {
                $this->tickets->add(new TicketRow($ticket));
            }
        }
        return $this->tickets;
    }

}