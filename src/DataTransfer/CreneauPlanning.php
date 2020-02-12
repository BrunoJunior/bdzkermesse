<?php

namespace App\DataTransfer;

use App\Entity\Benevole;
use App\Entity\Creneau;
use App\Entity\InscriptionBenevole;

class CreneauPlanning extends PlageHoraire
{

    /**
     * @var array|InfosBenevole[]
     */
    private $benevoles = [];

    /**
     * @var int
     */
    private $nbRequis;

    /**
     * @var int
     */
    private $nbValides;

    /**
     * @param Creneau $entity
     * @return static
     */
    public static function createFromEntity(Creneau $entity): self
    {
        $creneau = (new self());
        $creneau->nbRequis = $entity->getNbBenevolesRecquis();
        $benevolesValides = $entity->getInscriptionBenevoles()->filter(function (InscriptionBenevole $inscription) {
            return $inscription->getValidee();
        });
        $creneau->nbValides = count($benevolesValides);
        $creneau->setDebut($entity->getDebut())->setFin($entity->getFin());
        foreach ($benevolesValides as $inscription) {
            $creneau->addBenevole($inscription->getBenevole());
        }
        return $creneau;
    }

    /**
     * @return int
     */
    public function getTauxBenevoles(): int
    {
        return round($this->nbValides * 100 / $this->nbRequis);
    }

    /**
     * @return bool
     */
    public function isComplet(): bool
    {
        return $this->nbRequis === $this->nbValides;
    }

    /**
     * @return InfosBenevole[]|array
     */
    public function getBenevoles(): array
    {
        return $this->benevoles;
    }

    /**
     * @param Benevole $benevole
     * @return $this
     */
    public function addBenevole(Benevole $benevole): self
    {
        $this->benevoles[] = InfosBenevole::createFromEntity($benevole);
        return $this;
    }

    /**
     * Offset en % par rapport au début du planning
     * @param Planning $planning
     * @return int
     */
    public function getOffset(Planning $planning): int
    {
        $taillePlagePlanning = $planning->getTaillePlage();
        $offsetSec = $this->debut->getTimestamp() - $planning->getDebut()->getTimestamp();
        return round((100 * $offsetSec) / $taillePlagePlanning);
    }

    /**
     * La taille relative (en %) par rapport au planning
     * @param Planning $planning
     * @return int
     */
    public function getTailleRelative(Planning $planning): int
    {
        return round((100 * $this->getTaillePlage()) / $planning->getTaillePlage());
    }

    /**
     * Proportion sous la forme «1 / 4»
     * @return string
     */
    public function getProportion(): string
    {
        return "$this->nbValides / $this->nbRequis";
    }
}
