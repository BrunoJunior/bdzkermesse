<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RemboursementRepository")
 */
class Remboursement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $montant;

    /**
     * @ORM\Column(type="integer")
     */
    private $mode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numero_suivi;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Membre", inversedBy="remboursements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $membre_id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket", mappedBy="remboursement_id")
     */
    private $tickets;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
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

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(int $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getNumeroSuivi(): ?string
    {
        return $this->numero_suivi;
    }

    public function setNumeroSuivi(string $numero_suivi): self
    {
        $this->numero_suivi = $numero_suivi;

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

    /**
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setRemboursementId($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getRemboursementId() === $this) {
                $ticket->setRemboursementId(null);
            }
        }

        return $this;
    }
}
