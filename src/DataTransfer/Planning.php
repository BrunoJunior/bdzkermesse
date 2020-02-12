<?php

namespace App\DataTransfer;

use App\Entity\Kermesse;

/**
 * Class Planning
 * @package App\DataTransfer
 */
class Planning extends PlageHoraire
{

    /**
     * @var array|LignePlanning[]
     */
    private $lignes = [];

    /**
     * @param Kermesse $kermesse
     * @return static
     */
    public static function createFromKermesse(Kermesse $kermesse): self
    {
        $planning = new self();
        foreach ($kermesse->getActivites() as $activite) {
            if ($activite->getDate() === null) {
                continue;
            }
            $dateStr = $activite->getDate()->format('Ymd');
            if (!array_key_exists($dateStr, $planning->lignes)) {
                $planning->lignes[$dateStr] = (new LignePlanning())->setDate($activite->getDate());
            }
            $actPlanning = ActivitePlanning::createFromEntity($activite);
            $planning->recalculerExtremumAvecAutrePlage($actPlanning, true);
            $planning->lignes[$dateStr]->addActivite($actPlanning);
        }
        ksort($planning->lignes);
        return $planning;
    }

    /**
     * @return array|LignePlanning[]
     */
    public function getLignes(): array
    {
        return $this->lignes;
    }
}