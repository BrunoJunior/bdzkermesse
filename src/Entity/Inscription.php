<?php

namespace App\Entity;

use App\Repository\InscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InscriptionRepository::class)
 */
class Inscription
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etablissementNom;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $etablissementCodePostal;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $etablissementVille;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contactName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $contactEmail;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $contactMobile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $role;

    /**
     * @ORM\Column(type="integer")
     */
    private $state;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtablissementNom(): ?string
    {
        return $this->etablissementNom;
    }

    public function setEtablissementNom(string $etablissementNom): self
    {
        $this->etablissementNom = $etablissementNom;

        return $this;
    }

    public function getEtablissementCodePostal(): ?string
    {
        return $this->etablissementCodePostal;
    }

    public function setEtablissementCodePostal(string $etablissementCodePostal): self
    {
        $this->etablissementCodePostal = $etablissementCodePostal;

        return $this;
    }

    public function getEtablissementVille(): ?string
    {
        return $this->etablissementVille;
    }

    public function setEtablissementVille(string $etablissementVille): self
    {
        $this->etablissementVille = $etablissementVille;

        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): self
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactMobile(): ?string
    {
        return $this->contactMobile;
    }

    public function setContactMobile(string $contactMobile): self
    {
        $this->contactMobile = $contactMobile;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }
}
