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
use Doctrine\DBAL\DBALException;
use SimpleEnum\Exception\UnknownEumException;

class TicketRowGenerator
{
    /**
     * @var TicketRepository
     */
    private $rTicket;

    /**
     * @var DuplicataDirectoryGenerator
     */
    private $duplicataDirGen;

    /**
     * TicketRowGenerator constructor.
     * @param TicketRepository $rTicket
     * @param DuplicataDirectoryGenerator $duplicataDirGen
     */
    public function __construct(TicketRepository $rTicket, DuplicataDirectoryGenerator $duplicataDirGen)
    {
        $this->rTicket = $rTicket;
        $this->duplicataDirGen = $duplicataDirGen;
    }

    /**
     * @param Ticket $ticket
     * @param int $montantAffecte
     * @param array $activitesLiees
     * @return TicketRow
     * @throws DBALException
     * @throws UnknownEumException
     */
    public function generate(Ticket $ticket, int $montantAffecte = null, array $activitesLiees = null): TicketRow
    {
        $row = new TicketRow($ticket, $this->duplicataDirGen);
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
     * @param string $order
     * @return ArrayCollection|TicketRow[]
     * @throws DBALException
     * @throws UnknownEumException
     */
    public function generateList(Kermesse $kermesse, string $order): ArrayCollection
    {
        $tickets = $this->rTicket->findByKermesse($kermesse, $order);
        $totaux = $this->rTicket->getTotauxParTicketByKermesse($kermesse);
        $rows = new ArrayCollection();
        foreach ($tickets as $ticket) {
            $details = $totaux->get("".$ticket->getId()) ?? ['depense' => 0, 'activites_liees' => ''];
            $rows->add($this->generate($ticket, $details['depense'], explode(', ', $details['activites_liees'])));
        }
        return $rows;
    }
}
