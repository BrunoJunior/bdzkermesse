<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActiviteRepository")
 * @UniqueEntity(fields={"nom", "kermesse"})
 */
class Activite extends MyEntity
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

    /**
     * @ORM\Column(type="boolean")
     */
    private $accepte_tickets;

    /**
     * @ORM\Column(type="boolean")
     */
    private $accepte_monnaie;

    /**
     * @ORM\Column(type="boolean")
     */
    private $caisse_centrale;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etablissement")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etablissement;

    /**
     * @ORM\OneToMany(targetEntity="Creneau", mappedBy="activite", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"debut" = "ASC"})
     */
    private $creneaux;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
        $this->recettes = new ArrayCollection();
        $this->creneaux = new ArrayCollection();
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
        if ($kermesse instanceof Kermesse) {
            $this->setEtablissement($kermesse->getEtablissement());
            if ($kermesse->getMontantTicket() === 0) {
                $this->setAccepteSeulementMonnaie();
            }
        }
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
            $depense->setActivite($this);
        }
        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->contains($depense)) {
            $this->depenses->removeElement($depense);
            // set the owning side to null (unless already changed)
            if ($depense->getActivite() === $this) {
                $depense->setActivite(null);
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

    public function isAccepteTickets(): ?bool
    {
        return $this->accepte_tickets;
    }

    public function setAccepteTickets(bool $accepte_tickets): self
    {
        if (!$this->isCaisseCentrale()) {
            $this->accepte_tickets = $accepte_tickets;
        }
        return $this;
    }

    public function isAccepteMonnaie(): ?bool
    {
        return $this->accepte_monnaie;
    }

    public function setAccepteMonnaie(bool $accepte_monnaie): self
    {
        if (!$this->isCaisseCentrale()) {
            $this->accepte_monnaie = $accepte_monnaie;
        }
        return $this;
    }

    public function isCaisseCentrale(): ?bool
    {
        return $this->caisse_centrale;
    }

    public function setCaisseCentrale(bool $caisse_centrale): self
    {
        $this->caisse_centrale = $caisse_centrale;
        if ($caisse_centrale) {
            $this->setAccepteSeulementMonnaie();
        }
        return $this;
    }

    public function __clone()
    {
        $this->id = null;
        $this->kermesse = null;
        $this->depenses = new ArrayCollection();
        $this->recettes = new ArrayCollection();
    }

    public function setAccepteSeulementMonnaie(): self
    {
        $this->accepte_tickets = false;
        $this->accepte_monnaie = true;
        return $this;
    }

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    /**
     * @return Collection|Creneau[]
     */
    public function getCreneaux(): Collection
    {
        return $this->creneaux;
    }

    public function addCreneau(Creneau $creneau): self
    {
        if (!$this->creneaux->contains($creneau)) {
            $this->creneaux[] = $creneau;
            $creneau->setActivite($this);
        }

        return $this;
    }

    public function removeCreneau(Creneau $creneau): self
    {
        if ($this->creneaux->contains($creneau)) {
            $this->creneaux->removeElement($creneau);
            // set the owning side to null (unless already changed)
            if ($creneau->getActivite() === $this) {
                $creneau->setActivite(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
