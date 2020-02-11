<?php

namespace App\DataTransfer;

use App\Entity\Activite;

class ActivitePlanning
{
    /**
     * @var string
     */
    private $nom;

    /**
     * @var array|CreneauPlanning[]
     */
    private $creneaux = [];

    /**
     * @param Activite $entity
     * @return static
     */
    public static function createFromEntity(Activite $entity): self
    {
        $activite = (new self())->setNom($entity->getNom());
        foreach ($entity->getCreneaux() as $creneau) {
            $activite->addCreneau(CreneauPlanning::createFromEntity($creneau));
        }
        return $activite;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return $this
     */
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return array|CreneauPlanning[]
     */
    public function getCreneaux(): array
    {
        return $this->creneaux;
    }

    /**
     * @param CreneauPlanning $creneau
     * @return $this
     */
    public function addCreneau(CreneauPlanning $creneau): self
    {
        $this->creneaux[] = $creneau;
        return $this;
    }

}
