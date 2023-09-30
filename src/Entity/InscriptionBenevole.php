<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InscriptionBenevoleRepository")
 */
class InscriptionBenevole extends MyEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Benevole", inversedBy="inscriptionBenevoles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $benevole;

    /**
     * @ORM\ManyToOne(targetEntity="Creneau", inversedBy="inscriptionBenevoles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $inscription;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validee;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBenevole(): ?Benevole
    {
        return $this->benevole;
    }

    public function setBenevole(?Benevole $benevole): self
    {
        $this->benevole = $benevole;

        return $this;
    }

    public function getInscription(): ?Creneau
    {
        return $this->inscription;
    }

    public function setInscription(?Creneau $inscription): self
    {
        $this->inscription = $inscription;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getValidee(): ?bool
    {
        return $this->validee;
    }

    public function setValidee(bool $validee): self
    {
        $this->validee = $validee;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEtablissement(): ?Etablissement
    {
        $creneau = $this->getInscription();
        if ($creneau === null) {
            return null;
        }
        $activite = $creneau->getActivite();
        if ($activite === null) {
            return null;
        }
        return $activite->getEtablissement();
    }
}
