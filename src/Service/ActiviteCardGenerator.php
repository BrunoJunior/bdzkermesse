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
use App\Repository\ActiviteRepository;

class ActiviteCardGenerator
{
    /**
     * @var ActiviteRepository
     */
    private $rActivite;

    /**
     * ActiviteCard constructor.
     * @param ActiviteRepository $rActivite
     */
    public function __construct(ActiviteRepository $rActivite)
    {
        $this->rActivite = $rActivite;
    }

    /**
     * @param Activite $activite
     * @param int $depense
     * @param int $recette
     * @param int $nbTickets
     * @return ActiviteCard
     */
    public function generate(Activite $activite, int $depense = 0, int $recette = 0, int $nbTickets = 0): ActiviteCard
    {
        $card = new ActiviteCard($activite);
        return $card->setDepense($depense)
            ->setRecette($recette)
            ->setNombreTickets($nbTickets);
    }
}
