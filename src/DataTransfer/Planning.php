<?php

namespace App\DataTransfer;

use App\Entity\Kermesse;

/**
 * Class Planning
 * @package App\DataTransfer
 */
class Planning extends PlageHoraire
{

    use ProgressTrait;

    /**
     * @var array|LignePlanning[]
     */
    private $lignes = [];

    /**
     * @var int
     */
    private $idKermesse;

    /**
     * @param int $idKermesse
     * @param array $activites
     * @return static
     */
    public static function createFromKermesse(int $idKermesse, array $activites): self
    {
        $planning = new self();
        $planning->idKermesse = $idKermesse;
        foreach ($activites as $activite) {
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
            $planning->valuenow += $actPlanning->getValuenow();
            $planning->valuemax += $actPlanning->getValuemax();
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

    /**
     * @return int
     */
    public function getIdKermesse(): int
    {
        return $this->idKermesse;
    }
}
