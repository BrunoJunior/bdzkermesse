<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 16:49
 */

namespace App\Service;


use App\DataTransfer\TicketRow;
use App\Entity\Kermesse;
use App\Entity\Ticket;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;

class TicketRowGenerator
{
    /**
     * @var TicketRepository
     */
    private $rTicket;

    /**
     * TicketRowGenerator constructor.
     * @param TicketRepository $rTicket
     */
    public function __construct(TicketRepository $rTicket)
    {
        $this->rTicket = $rTicket;
    }

    /**
     * @param Ticket $ticket
     * @param int $montantAffecte
     * @param string $activitesLiees
     * @return TicketRow
     */
    public function generate(Ticket $ticket, int $montantAffecte, string $activitesLiees): TicketRow
    {
        $row = new TicketRow($ticket);
        $row->setActivitesLiees($activitesLiees);
        $row->setMontantAffecte($montantAffecte);
        return $row;
    }

    /**
     * @param Kermesse $kermesse
     * @return ArrayCollection|TicketRow[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function generateList(Kermesse $kermesse): ArrayCollection
    {
        $tickets = $this->rTicket->findByKermesse($kermesse);
        $totaux = $this->rTicket->getTotauxParTicketByKermesse($kermesse);
        $rows = new ArrayCollection();
        foreach ($tickets as $ticket) {
            $details = $totaux->get("".$ticket->getId()) ?? ['depense' => 0, 'activites_liees' => string];
            $rows->add($this->generate($ticket, $details['depense'], $details['activites_liees']));
        }
        return $rows;
    }
}