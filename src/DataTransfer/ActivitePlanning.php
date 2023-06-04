<?php

namespace App\DataTransfer;

use App\Entity\Activite;

class ActivitePlanning extends PlageHoraire
{

    use ProgressTrait;

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
     * @var string|null
     */
    private $description;

    /**
     * @var bool|null
     */
    private $onlyForPlanning;

    /**
     * @param Activite $entity
     * @return static
     */
    public static function createFromEntity(Activite $entity): self
    {
        $activite = (new self())
            ->setNom($entity->getNom())
            ->setId($entity->getId())
            ->setDescription($entity->getDescription())
            ->setOnlyForPlanning($entity->isOnlyForPlanning());
        foreach ($entity->getCreneaux() as $creneau) {
            $creneauPlanning = CreneauPlanning::createFromEntity($creneau);
            $activite->addCreneau($creneauPlanning);
            $activite->valuenow += $creneauPlanning->getNbValides();
            $activite->valuemax += $creneauPlanning->getNbRequis();
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
     * @return array|LigneCreneaux[]
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

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return ActivitePlanning
     */
    public function setDescription(?string $description): ActivitePlanning
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isOnlyForPlanning(): bool
    {
        return $this->onlyForPlanning;
    }

    /**
     * @param bool $onlyForPlanning
     * @return ActivitePlanning
     */
    public function setOnlyForPlanning(bool $onlyForPlanning): ActivitePlanning
    {
        $this->onlyForPlanning = $onlyForPlanning;
        return $this;
    }

}
