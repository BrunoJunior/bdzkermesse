<?php

namespace App\DataTransfer;

use App\Entity\Benevole;

class InfosBenevole
{
    /**
     * @var string
     */
    private $identite;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $tel;

    /**
     * @param Benevole $entity
     * @return static
     */
    public static function createFromEntity(Benevole $entity): self
    {
        $benevole = new self();
        $benevole->identite = $entity->getIdentite();
        $benevole->email = $entity->getEmail();
        $benevole->tel = $entity->getPortable();
        return $benevole;
    }

    /**
     * @return string
     */
    public function getIdentite(): string
    {
        return $this->identite;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTel(): string
    {
        return $this->tel;
    }
}
