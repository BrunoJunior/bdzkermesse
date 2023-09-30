<?php


namespace App\DataTransfer;


use App\Entity\Activite;
use App\Entity\Creneau;

class RecapitulatifBenevoleActivite
{
    /**
     * @var Activite
     */
    public $activite;

    /**
     * @var array|Creneau[]
     */
    public $creneaux = [];

    /**
     * RecapitulatifBenevoleActivite constructor.
     * @param Activite $activite
     * @param Creneau $creneau
     */
    public function __construct(Activite $activite, Creneau $creneau)
    {
        $this->activite = $activite;
        $this->creneaux[] = $creneau;
    }

    /**
     * @param Creneau $creneau
     * @return $this
     */
    public function addCreneau(Creneau $creneau): self
    {
        $this->creneaux[] = $creneau;
        return $this;
    }
}
