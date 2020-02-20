<?php

namespace App\DataTransfer;

use App\Entity\InscriptionBenevole;

class InfosBenevole
{
    /**
     * @var int
     */
    private $idInscription;

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
     * @var int
     */
    private $id;

    /**
     * @param InscriptionBenevole $entity
     * @return static
     */
    public static function createFromEntity(InscriptionBenevole $entity): self
    {
        $benevole = $entity->getBenevole();
        $infos = new self();
        $infos->idInscription = $entity->getId();
        $infos->identite = $benevole->getIdentite();
        $infos->email = $benevole->getEmail();
        $infos->tel = $benevole->getPortable();
        $infos->id = $benevole->getId();
        return $infos;
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

    /**
     * @return int
     */
    public function getIdInscription(): int
    {
        return $this->idInscription;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
