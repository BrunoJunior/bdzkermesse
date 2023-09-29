<?php

namespace App\DataTransfer;

use App\Entity\Creneau;

class Inscription
{
    /**
     * @var Creneau
     */
    private $creneau;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $portable;

    /**
     * @var string
     */
    private $nom;

    /**
     * @return Creneau|null
     */
    public function getCreneau(): ?Creneau
    {
        return $this->creneau;
    }

    /**
     * @param Creneau $creneau
     * @return Inscription
     */
    public function setCreneau(Creneau $creneau): Inscription
    {
        $this->creneau = $creneau;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Inscription
     */
    public function setEmail(string $email): Inscription
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPortable(): ?string
    {
        return $this->portable;
    }

    /**
     * @param string $portable
     * @return Inscription
     */
    public function setPortable(string $portable): Inscription
    {
        $this->portable = $portable;
        return $this;
    }

    /**
     * @return string
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return Inscription
     */
    public function setNom(string $nom): Inscription
    {
        $this->nom = $nom;
        return $this;
    }

}
