<?php

namespace App\DataTransfer;

use App\Entity\Activite;

class ActivitePlanning extends PlageHoraire
{

    /**
     * @var string
     */
    private $nom;

    /**
     * @var int
     */
    private $id;

    /**
     * @var array|LigneCreneaux[]
     */
    private $lignesCreneaux = [];

    /**
     * @param Activite $entity
     * @return static
     */
    public static function createFromEntity(Activite $entity): self
    {
        $activite = (new self())->setNom($entity->getNom())->setId($entity->getId());
        foreach ($entity->getCreneaux() as $creneau) {
            $activite->addCreneau(CreneauPlanning::createFromEntity($creneau));
        }
        return $activite;
    }

    /**
     * @param CreneauPlanning $creneauPlanning
     * @return $this
     */
    private function addCreneau(CreneauPlanning $creneauPlanning): self
    {
        // Calcul des extremum
        $this->recalculerExtremumAvecAutrePlage($creneauPlanning);
        $ajoute = false;
        // On essaie d'ajouter le créneau à la première ligne
        // Si ça ne marche pas, on réessaie avec la ligne suivante
        // Jusqu'à ce que ça passe sur une ligne ou que ça ai râté sur toutes les lignes
        foreach ($this->lignesCreneaux as $ligneCreneau) {
            if ($ligneCreneau->addCreneau($creneauPlanning) !== null) {
                $ajoute = true;
                break;
            }
        }
        // Ça a râté sur toutes les lignes, on en crée une nouvelle et on y ajoute le créneau
        if (!$ajoute) {
            $this->lignesCreneaux[] = (new LigneCreneaux())->addCreneau($creneauPlanning);
        }
        return $this;
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
    public function getLignesCreneaux(): array
    {
        return $this->lignesCreneaux;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

}
