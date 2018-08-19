<?php

namespace App\Entity;

use App\Helper\HFloat;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TicketRepository")
 * @UniqueEntity(fields={"numero", "kermesse"})
 */
class Ticket extends MyEntity
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
    private $numero;

    /**
     * @ORM\Column(type="integer")
     */
    private $montant;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fournisseur;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Kermesse", inversedBy="tickets")
     * @ORM\JoinColumn(name="kermesse_id", referencedColumnName="id", nullable=false)
     */
    private $kermesse;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Membre", inversedBy="tickets")
     * @ORM\JoinColumn(name="membre_id", referencedColumnName="id", nullable=true)
     */
    private $membre;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Remboursement", inversedBy="tickets")
     * @ORM\JoinColumn(name="remboursement_id", referencedColumnName="id", nullable=true)
     */
    private $remboursement;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Depense", mappedBy="ticket", orphanRemoval=true, cascade={"persist"})
     */
    private $depenses;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etablissement")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etablissement;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
        $this->date = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getFournisseur(): ?string
    {
        return $this->fournisseur;
    }

    public function setFournisseur(string $fournisseur): self
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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
        }
        return $this;
    }

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre): self
    {
        $this->membre = $membre;

        return $this;
    }

    public function getRemboursement(): ?Remboursement
    {
        return $this->remboursement;
    }

    public function setRemboursement(?Remboursement $remboursement): self
    {
        $this->remboursement = $remboursement;

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
        $depense->setTicket($this);
        if (!$this->depenses->contains($depense)) {
            $this->depenses[] = $depense;
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->contains($depense)) {
            $this->depenses->removeElement($depense);
            // set the owning side to null (unless already changed)
            if ($depense->getTicket() === $this) {
                $depense->setTicket(null);
            }
        }

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
}
