<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 19/03/2019
 * Time: 17:52
 */

namespace App\Service;

use App\Business\MembreBusiness;
use App\DataTransfer\LigneComptable;
use App\Entity\Recette;
use App\Entity\Ticket;

/**
 * Class LigneComptableGenerator
 * @package App\Service
 */
class LigneComptableGenerator
{

    /**
     * @var MembreBusiness
     */
    private $bMembre;

    /**
     * LigneComptableGenerator constructor.
     * @param MembreBusiness $bMembre
     */
    public function __construct(MembreBusiness $bMembre)
    {
        $this->bMembre = $bMembre;
    }

    /**
     * @param Ticket $ticket
     * @return LigneComptable
     */
    public function fromTicket(Ticket $ticket): LigneComptable
    {
        $membre = $ticket->getMembre();
        return new LigneComptable($ticket->getDate(), "{$ticket->getFournisseur()} - {$ticket->getNumero()} ({$this->bMembre->getIdentite($membre)})", -$ticket->getMontant());
    }

    /**
     * @param Recette $recette
     * @return LigneComptable
     */
    public function fromRecette(Recette $recette): LigneComptable
    {
        return new LigneComptable($recette->getDate(), "{$recette->getActivite()->getNom()} / {$recette->getLibelle()}", $recette->getMontant());
    }
}