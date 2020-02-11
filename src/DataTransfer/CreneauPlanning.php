<?php

namespace App\DataTransfer;

use App\Entity\Creneau;
use App\Entity\InscriptionBenevole;
use DateTimeInterface;

class CreneauPlanning
{
    /**
     * @var DateTimeInterface
     */
    private $debut;

    /**
     * @var DateTimeInterface
     */
    private $fin;

    /**
     * @var int
     */
    private $tauxBenevoles;

    /**
     * @var bool
     */
    private $complet;

    /**
     * @var array|string[]
     */
    private $benevoles = [];

    /**
     * @param Creneau $entity
     * @return static
     */
    public static function createFromEntity(Creneau $entity): self
    {
        $nbBenevolesRequis = $entity->getNbBenevolesRecquis();
        $benevolesValides = $entity->getInscriptionBenevoles()->filter(function (InscriptionBenevole $inscription) {
            return $inscription->getValidee();
        });
        $creneau = (new self())
            ->setDebut($entity->getDebut())
            ->setFin($entity->getFin())
            ->setComplet($nbBenevolesRequis === $benevolesValides->count())
            ->setTauxBenevoles(round($benevolesValides->count() * 100 / $nbBenevolesRequis));
        foreach ($benevolesValides as $inscription) {
            $creneau->addBenevole($inscription->getBenevole()->getIdentite());
        }
        return $creneau;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDebut(): DateTimeInterface
    {
        return $this->debut;
    }

    /**
     * @param DateTimeInterface $debut
     * @return $this
     */
    public function setDebut(DateTimeInterface $debut): self
    {
        $this->debut = $debut;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getFin(): DateTimeInterface
    {
        return $this->fin;
    }

    /**
     * @param DateTimeInterface $fin
     * @return $this
     */
    public function setFin(DateTimeInterface $fin): self
    {
        $this->fin = $fin;
        return $this;
    }

    /**
     * @return int
     */
    public function getTauxBenevoles(): int
    {
        return $this->tauxBenevoles;
    }

    /**
     * @param int $tauxBenevoles
     * @return $this
     */
    public function setTauxBenevoles(int $tauxBenevoles): self
    {
        $this->tauxBenevoles = $tauxBenevoles;
        return $this;
    }

    /**
     * @return bool
     */
    public function isComplet(): bool
    {
        return $this->complet;
    }

    /**
     * @param bool $complet
     * @return $this
     */
    public function setComplet(bool $complet): self
    {
        $this->complet = $complet;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getBenevoles()
    {
        return $this->benevoles;
    }

    /**
     * @param string $benevole
     * @return $this
     */
    public function addBenevole(string $benevole): self
    {
        $this->benevoles[] = $benevole;
        return $this;
    }
}
