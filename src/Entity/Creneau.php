<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CreneauRepository")
 */
class Creneau
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="time")
     */
    private $debut;

    /**
     * @ORM\Column(type="time")
     */
    private $fin;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite", inversedBy="creneaux")
     * @ORM\JoinColumn(nullable=false)
     */
    private $activite;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbBenevolesRecquis;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\InscriptionBenevole", mappedBy="inscription", orphanRemoval=true)
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

    public function getDebut(): ?DateTimeInterface
    {
        return $this->debut;
    }

    public function setDebut(DateTimeInterface $debut): self
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?DateTimeInterface
    {
        return $this->fin;
    }

    public function setFin(DateTimeInterface $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): self
    {
        $this->activite = $activite;

        return $this;
    }

    public function getNbBenevolesRecquis(): ?int
    {
        return $this->nbBenevolesRecquis;
    }

    public function setNbBenevolesRecquis(int $nbBenevolesRecquis): self
    {
        $this->nbBenevolesRecquis = $nbBenevolesRecquis;

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
            $inscriptionBenevole->setInscription($this);
        }

        return $this;
    }

    public function removeInscriptionBenevole(InscriptionBenevole $inscriptionBenevole): self
    {
        if ($this->inscriptionBenevoles->contains($inscriptionBenevole)) {
            $this->inscriptionBenevoles->removeElement($inscriptionBenevole);
            // set the owning side to null (unless already changed)
            if ($inscriptionBenevole->getInscription() === $this) {
                $inscriptionBenevole->setInscription(null);
            }
        }

        return $this;
    }
}
