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
    const NOM_CAISSE_CENT = 'Caisse centrale';

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
     * @ORM\JoinColumn(name="kermesse_id", referencedColumnName="id", nullable=true)
     */
    private $kermesse;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Depense", mappedBy="activite", orphanRemoval=true)
     */
    private $depenses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Recette", mappedBy="activite", orphanRemoval=true)
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

    public function getKermesse(): ?Kermesse
    {
        return $this->kermesse;
    }

    public function setKermesse(?Kermesse $kermesse): self
    {
        $this->kermesse = $kermesse;
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
            $recette->setActivite($this);
        }
        return $this;
    }

    public function removeRecette(Recette $recette): self
    {
        if ($this->recettes->contains($recette)) {
            $this->recettes->removeElement($recette);
            // set the owning side to null (unless already changed)
            if ($recette->getActivite() === $this) {
                $recette->setActivite(null);
            }
        }
        return $this;
    }

    /**
     * La recette en monaie de l'activité
     * @return int
     */
    public function getMontantRecette(): int
    {
        $total = 0;
        foreach ($this->getRecettes() as $recette) {
            $total += $recette->getMontant();
        }
        return $total;
    }

    /**
     * Le montant des dépenses de l'activité
     * @return int
     */
    public function getMontantDepense(): int
    {
        $total = 0;
        foreach ($this->getDepenses() as $depense) {
            $total += $depense->getMontant();
        }
        return $total;
    }

    /**
     * La balance de l'activité en prenant en compte les tickets
     * Cette balance est une approximation, car la vraie recette ne tient pas compte des tickets car ces derniers
     * ont dus être achetés et donc la somme est comptabilisée dans la balance globale
     * @return int
     */
    public function getBalance(): int
    {
        return $this->getMontantRecette() - $this->getMontantDepense() + ($this->getKermesse()->getMontantTicket() * $this->getNombreTotalTickets());
    }

    /**
     * Le nombre total de ticket de l'activité
     * @return int
     */
    public function getNombreTotalTickets(): int
    {
        $total = 0;
        foreach ($this->getRecettes() as $recette) {
            $total += $recette->getNombreTicket();
        }
        return $total;
    }
}
