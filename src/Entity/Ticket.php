<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TicketRepository")
 */
class Ticket
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
     * @ORM\JoinColumn(nullable=false)
     */
    private $kermesse_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Membre", inversedBy="tickets")
     */
    private $membre_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Remboursement", inversedBy="tickets")
     */
    private $remboursement_id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Depense", mappedBy="ticket_id", orphanRemoval=true)
     */
    private $depenses;

    public function __construct()
    {
        $this->depenses = new ArrayCollection();
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

    public function getKermesseId(): ?Kermesse
    {
        return $this->kermesse_id;
    }

    public function setKermesseId(?Kermesse $kermesse_id): self
    {
        $this->kermesse_id = $kermesse_id;

        return $this;
    }

    public function getMembreId(): ?Membre
    {
        return $this->membre_id;
    }

    public function setMembreId(?Membre $membre_id): self
    {
        $this->membre_id = $membre_id;

        return $this;
    }

    public function getRemboursementId(): ?Remboursement
    {
        return $this->remboursement_id;
    }

    public function setRemboursementId(?Remboursement $remboursement_id): self
    {
        $this->remboursement_id = $remboursement_id;

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
            $depense->setTicketId($this);
        }

        return $this;
    }

    public function removeDepense(Depense $depense): self
    {
        if ($this->depenses->contains($depense)) {
            $this->depenses->removeElement($depense);
            // set the owning side to null (unless already changed)
            if ($depense->getTicketId() === $this) {
                $depense->setTicketId(null);
            }
        }

        return $this;
    }
}
