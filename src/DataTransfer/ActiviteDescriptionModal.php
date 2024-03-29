<?php

namespace App\DataTransfer;

use App\Entity\Activite;

class ActiviteDescriptionModal
{
    /**
     * @var string|null
     */
    private $type;
    /**
     * @var string|null
     */
    private $description;
    /**
     * @var string|null
     */
    private $regle;
    /**
     * @var int
     */
    private $nbTickets;

    /**
     * @param Activite $activite
     */
    public function __construct(Activite $activite)
    {
        $type = $activite->getType();
        $this->type = $type ? $type->getNom() : null;
        $this->description = $activite->getDescription();
        $this->regle = $activite->getRegle();
        $this->nbTickets = $activite->getNbTickets() ?: 0;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getRegle(): ?string
    {
        return $this->regle;
    }

    /**
     * @return int
     */
    public function getNbTickets(): int
    {
        return $this->nbTickets;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return !$this->description && !$this->type && !$this->regle;
    }
}