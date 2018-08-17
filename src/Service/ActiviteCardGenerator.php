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
     * @param Kermesse $kermesse
     * @return array|ActiviteCard[]
     */
    public function generateList(Kermesse $kermesse): array
    {
        $totaux = $this->rActivite->getTotaux($kermesse);
        $cards = array_map(function (Activite $activite) use($totaux) {
            $card = new ActiviteCard($activite);
            $key = '' . $activite->getId();
            if ($totaux->containsKey($key)) {
                $card->setDepense($totaux->get($key)['depense'])
                    ->setNombreTickets($totaux->get($key)['nombre_ticket'])
                    ->setRecette($totaux->get($key)['recette']);
            }
            return $card;
        }, $this->rActivite->findByKermesseId($kermesse->getId()));
        return $cards;
    }
}