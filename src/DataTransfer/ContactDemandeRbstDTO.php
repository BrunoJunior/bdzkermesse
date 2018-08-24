<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 15:48
 */

namespace App\DataTransfer;

use App\Business\TicketBusiness;
use App\Entity\Remboursement;
use App\Helper\HFloat;
use Doctrine\Common\Collections\ArrayCollection;
use Stringy\Stringy;

class ContactDemandeRbstDTO extends ContactDTO
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
     * @var TicketBusiness
     */
    private $bTicket;

    /**
     * ContactDemandeRbstDTO constructor.
     * @param TicketBusiness $bTicket
     */
    public function __construct(TicketBusiness $bTicket)
    {
        $this->bTicket = $bTicket;
    }

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

    /**
     * @return array
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function getMessageVars(): array
    {
        return [
            'message' => $this->getMessage(),
            'montant' => $this->getMontant(),
            'tickets' => $this->getTickets(),
            'numero_suivi' => $this->remboursement->getNumeroSuivi()
        ];
    }

    /**
     * @param \Swift_Message $message
     */
    public function completerMessage(\Swift_Message $message)
    {
        foreach ($this->remboursement->getTickets() as $ticket) {
            $duplicata = $ticket->getDuplicata();
            if ($duplicata) {
                $filepath = $this->bTicket->getDuplicataDir($ticket->getKermesse()) . '/' . $duplicata;
                $message->attach(\Swift_Attachment::fromPath($filepath)->setFilename($ticket->getNumero() . '.' . pathinfo($filepath,PATHINFO_EXTENSION)));
            }
        }
    }

}