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
     * @param Activite $activite
     */
    public function __construct(Activite $activite)
    {
        $type = $activite->getType();
        $this->type = $type ? $type->getNom() : null;
        $this->description = $activite->getDescription();
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
     * @return bool
     */
    public function isEmpty(): bool {
        return !$this->description && !$this->type;
    }
}