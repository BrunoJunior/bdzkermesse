<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 16:49
 */

namespace App\Service;


use App\DataTransfer\TicketRow;
use App\Entity\Ticket;

class TicketRowGenerator
{
    /**
     * @param Ticket $ticket
     * @return TicketRow
     */
    public function generate(Ticket $ticket): TicketRow
    {
        return new TicketRow($ticket);
    }
}