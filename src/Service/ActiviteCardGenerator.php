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
use App\Entity\Kermesse;
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

    /**
     * @param Kermesse $kermesse
     * @return array|ActiviteCard[]
     */
    public function generateList(Kermesse $kermesse): array
    {
        $totaux = $this->rActivite->getTotaux($kermesse);
        $cards = array_map(function (Activite $activite) use($totaux) {
            $key = '' . $activite->getId();
            if ($totaux->containsKey($key)) {
                return $this->generate($activite, $totaux->get($key)['depense'], $totaux->get($key)['recette'], $totaux->get($key)['nombre_ticket']);
            }
            return $this->generate($activite);
        }, $this->rActivite->findByKermesseId($kermesse->getId()));
        return $cards;
    }
}