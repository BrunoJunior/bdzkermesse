<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 10:51
 */

namespace App\Service;


use App\DataTransfer\ActiviteCard;
use App\Entity\Activite;
use App\Repository\DepenseRepository;
use App\Repository\RecetteRepository;

class ActiviteCardGenerator
{
    /**
     * @var RecetteRepository
     */
    private $rRecette;

    /**
     * @var DepenseRepository
     */
    private $rDepense;

    /**
     * ActiviteCard constructor.
     * @param RecetteRepository $rRecette
     * @param DepenseRepository $rDepense
     */
    public function __construct(RecetteRepository $rRecette, DepenseRepository $rDepense)
    {
        $this->rRecette = $rRecette;
        $this->rDepense = $rDepense;
    }

    /**
     * @param Activite $activite
     * @return ActiviteCard
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function generate(Activite $activite): ActiviteCard
    {
        $recette = $this->rRecette->getTotauxPourActivite($activite);
        $card = new ActiviteCard($activite);
        return $card->setDepense($this->rDepense->getMontantTotalPourActivite($activite))
            ->setNombreTickets($recette['nombre_ticket'])
            ->setRecette($recette['montant']);
    }
}