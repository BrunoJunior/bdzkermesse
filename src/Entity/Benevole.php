<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BenevoleRepository")
 */
class Benevole
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $identite;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $emailValide;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $portable;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\InscriptionBenevole", mappedBy="benevole", orphanRemoval=true)
     */
    private $inscriptionBenevoles;

    public function __construct()
    {
        $this->inscriptionBenevoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentite(): ?string
    {
        return $this->identite;
    }

    public function setIdentite(string $identite): self
    {
        $this->identite = $identite;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmailValide(): ?bool
    {
        return $this->emailValide;
    }

    public function setEmailValide(bool $emailValide): self
    {
        $this->emailValide = $emailValide;

        return $this;
    }

    public function getPortable(): ?string
    {
        return $this->portable;
    }

    public function setPortable(?string $portable): self
    {
        $this->portable = $portable;

        return $this;
    }

    /**
     * @return Collection|InscriptionBenevole[]
     */
    public function getInscriptionBenevoles(): Collection
    {
        return $this->inscriptionBenevoles;
    }

    public function addInscriptionBenevole(InscriptionBenevole $inscriptionBenevole): self
    {
        if (!$this->inscriptionBenevoles->contains($inscriptionBenevole)) {
            $this->inscriptionBenevoles[] = $inscriptionBenevole;
            $inscriptionBenevole->setBenevole($this);
        }

        return $this;
    }

    public function removeInscriptionBenevole(InscriptionBenevole $inscriptionBenevole): self
    {
        if ($this->inscriptionBenevoles->contains($inscriptionBenevole)) {
            $this->inscriptionBenevoles->removeElement($inscriptionBenevole);
            // set the owning side to null (unless already changed)
            if ($inscriptionBenevole->getBenevole() === $this) {
                $inscriptionBenevole->setBenevole(null);
            }
        }

        return $this;
    }
}
