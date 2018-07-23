<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActiviteRepository")
 */
class Activite
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
    private $nom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Kermesse", inversedBy="activites")
     */
    private $kermesse_id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Depense", mappedBy="activite_id", orphanRemoval=true)
     */
    private $depenses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Recette", mappedBy="activite_id", orphanRemoval=true)
     */
    private $recettes;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
        $this->recettes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getKermesseId(): ?Kermesse
    {
        return $this->kermesse_id;
    }

    public function setKermesseId(?Kermesse $kermesse_id): self
    {
        $this->kermesse_id = $kermesse_id;

        return $this;
    }

    /**
     * @return Collection|Depense[]
     */
    public function getDepenses(): Collection
    {
        return $this->depenses;
    }

    public function addDepense(Depense $depense): self
    {
        if (!$this->depenses->contains($depense)) {
            $this->depenses[] = $depense;
            $depense->setActiviteId($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->contains($depense)) {
            $this->depenses->removeElement($depense);
            // set the owning side to null (unless already changed)
            if ($depense->getActiviteId() === $this) {
                $depense->setActiviteId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Recette[]
     */
    public function getRecettes(): Collection
    {
        return $this->recettes;
    }

    public function addRecette(Recette $recette): self
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes[] = $recette;
            $recette->setActiviteId($this);
        }

        return $this;
    }

    public function removeRecette(Recette $recette): self
    {
        if ($this->recettes->contains($recette)) {
            $this->recettes->removeElement($recette);
            // set the owning side to null (unless already changed)
            if ($recette->getActiviteId() === $this) {
                $recette->setActiviteId(null);
            }
        }

        return $this;
    }
}
