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
     * @param array $activitesLiees
     * @return TicketRow
     * @throws \Doctrine\DBAL\DBALException
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function generate(Ticket $ticket, int $montantAffecte = null, array $activitesLiees = null): TicketRow
    {
        $row = new TicketRow($ticket);
        if ($montantAffecte === null && $activitesLiees === null) {
            $totaux = $this->rTicket->getTotauxByTicket($ticket);
            $activitesLiees = explode(', ', $totaux['activites_liees']);
            $montantAffecte = $totaux['depense'];
        }
        $row->setActivitesLiees($activitesLiees);
        $row->setMontantAffecte($montantAffecte);
        return $row;
    }

    /**
     * @param Kermesse $kermesse
     * @return ArrayCollection|TicketRow[]
     * @throws \Doctrine\DBAL\DBALException
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function generateList(Kermesse $kermesse): ArrayCollection
    {
        $tickets = $this->rTicket->findByKermesse($kermesse);
        $totaux = $this->rTicket->getTotauxParTicketByKermesse($kermesse);
        $rows = new ArrayCollection();
        foreach ($tickets as $ticket) {
            $details = $totaux->get("".$ticket->getId()) ?? ['depense' => 0, 'activites_liees' => ''];
            $rows->add($this->generate($ticket, $details['depense'], explode(', ', $details['activites_liees'])));
        }
        return $rows;
    }
}