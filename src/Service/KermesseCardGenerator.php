<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 10:51
 */

namespace App\Service;

use App\DataTransfer\KermesseCard;
use App\Entity\Kermesse;
use App\Repository\RecetteRepository;
use App\Repository\TicketRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class KermesseCardGenerator
{

    /**
     * @var RecetteRepository
     */
    private $rRecette;

    /**
     * @var TicketRepository
     */
    private $rTicket;

    /**
     * KermesseDto constructor.
     * @param RecetteRepository $rRecette
     * @param TicketRepository $rTicket
     */
    public function __construct(RecetteRepository $rRecette, TicketRepository $rTicket)
    {
        $this->rRecette = $rRecette;
        $this->rTicket = $rTicket;
    }

    /**
     * @param Kermesse $kermesse
     * @return KermesseCard
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function generate(Kermesse $kermesse): KermesseCard
    {
        $card = new KermesseCard($kermesse);
        $totaux = $this->rRecette->getTotauxPourKermesse($kermesse);
        return $card->setDepense($this->rTicket->getMontantTotalPourKermesse($kermesse))
            ->setRecette($totaux['montant']);
    }
}
