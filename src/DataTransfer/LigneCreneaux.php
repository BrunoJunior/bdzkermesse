<?php

namespace App\DataTransfer;

class LigneCreneaux extends PlageHoraire
{

    /**
     * @var array|CreneauPlanning[]
     */
    private $creneaux = [];

    /**
     * Ajoute un créneau
     * @param CreneauPlanning $creneau
     * @return null|$this
     */
    public function addCreneau(CreneauPlanning $creneau): ?self
    {
        if ($this->debut && $this->fin && $this->chevaucheAutrePlage($creneau)) {
            return null;
        }
        $this->creneaux[] = $creneau;
        $this->recalculerExtremumAvecAutrePlage($creneau);
        return $this;
    }

    /**
     * @return array|CreneauPlanning[]
     */
    public function getCreneaux(): array
    {
        usort($this->creneaux, function (CreneauPlanning $creneau1, CreneauPlanning $creneau2) {
            return $creneau1->getDebut()->getTimestamp() - $creneau2->getDebut()->getTimestamp();
        });
        return $this->creneaux;
    }

    /**
     * Tous les créneaux sont vides
     * @return bool
     */
    public function isEmpty(): bool {
        foreach ($this->creneaux as $creneau) {
            if (!$creneau->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return int
     */
    public function getNbBenevolesRequis(): int {
        return array_reduce($this->creneaux,function (int $sum, CreneauPlanning $creneau) {
            return $sum + $creneau->getNbRequis();
        },0);
    }
}
