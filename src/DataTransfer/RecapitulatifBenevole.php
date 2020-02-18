<?php

namespace App\DataTransfer;

use App\Entity\Benevole;
use App\Entity\Creneau;

/**
 * Class RecapitulatifBenevole
 * @package App\DataTransfer
 */
class RecapitulatifBenevole
{
    /**
     * @var Benevole
     */
    public $benevole;

    /**
     * @var RecapitulatifBenevoleActivite[]|array
     */
    public $activites = [];

    /**
     * RecapitulatifBenevole constructor.
     * @param Benevole $benevole
     */
    public function __construct(Benevole $benevole)
    {
        $this->benevole = $benevole;
    }

    /**
     * @param Creneau $creneau
     * @return $this
     */
    public function addCreneau(Creneau $creneau): self
    {
        $activite = $creneau->getActivite();
        if (!array_key_exists($activite->getId(), $this->activites)) {
            $this->activites[$activite->getId()] = new RecapitulatifBenevoleActivite($activite, $creneau);
        } else {
            $this->activites[$activite->getId()]->addCreneau($creneau);
        }
        return $this;
    }
}
