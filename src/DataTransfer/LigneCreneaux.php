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
}
