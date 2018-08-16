<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 16/08/2018
 * Time: 15:49
 */

namespace App\DataTransfer;
use App\Entity\Kermesse;
use App\Helper\HFloat;
use App\Repository\RecetteRepository;
use App\Repository\TicketRepository;

/**
 * Class KermesseCard
 * @package App\DataTransfer
 */
class KermesseCard
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
     */
    public function __construct(RecetteRepository $rRecette, TicketRepository $rTicket)
    {
        $this->rRecette = $rRecette;
        $this->rTicket = $rTicket;
    }

    /**
     * Export à destination de l'IHM
     * @param Kermesse $kermesse
     * @return \stdClass
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generer(Kermesse $kermesse):\stdClass
    {
        $recette = $this->rRecette->getMontantTotalPourKermesse($kermesse);
        $depense = $this->rTicket->getMontantTotalPourKermesse($kermesse);
        $card = new \stdClass();
        $card->id = $kermesse->getId();
        $card->titre = $kermesse->getAnnee() . ' - ' . $kermesse->getTheme();
        $card->montantTicket = HFloat::getInstance($kermesse->getMontantTicket() / 100.0)->getMontantFormatFrancais();
        $card->recetteTotale = HFloat::getInstance($recette / 100.00)->getMontantFormatFrancais();
        $card->depenseTotale = HFloat::getInstance($depense / 100.00)->getMontantFormatFrancais();
        $card->balance = HFloat::getInstance(($recette - $depense) / 100.00)->getMontantFormatFrancais();
        return $card;
    }
}