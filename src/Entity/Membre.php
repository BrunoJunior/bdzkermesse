<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MembreRepository")
 */
class Membre extends MyEntity
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
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @var Etablissement
     * @ORM\ManyToOne(targetEntity="App\Entity\Etablissement", inversedBy="membres")
     * @ORM\JoinColumn(name="etablissement_id", referencedColumnName="id", nullable=true)
     */
    private $etablissement;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Kermesse", mappedBy="membres")
     */
    private $kermesses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Remboursement", mappedBy="membre", orphanRemoval=true)
     */
    private $remboursements;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket", mappedBy="membre")
     */
    private $tickets;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $defaut = false;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $gestionnaire = false;

    public function __construct()
    {
        $this->kermesses = new ArrayCollection();
        $this->remboursements = new ArrayCollection();
        $this->tickets = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return Collection|Kermesse[]
     */
    public function getKermesses(): Collection
    {
        return $this->kermesses;
    }

    public function addKermesse(Kermesse $kermesse): self
    {
        if (!$this->kermesses->contains($kermesse)) {
            $this->kermesses[] = $kermesse;
        }

        return $this;
    }

    public function removeKermesse(Kermesse $kermesse): self
    {
        if ($this->kermesses->contains($kermesse)) {
            $this->kermesses->removeElement($kermesse);
        }

        return $this;
    }

    /**
     * @return Collection|Remboursement[]
     */
    public function getRemboursements(): Collection
    {
        return $this->remboursements;
    }

    public function addRemboursement(Remboursement $remboursement): self
    {
        if (!$this->remboursements->contains($remboursement)) {
            $this->remboursements[] = $remboursement;
            $remboursement->setMembre($this);
        }

        return $this;
    }

    public function removeRemboursement(Remboursement $remboursement): self
    {
        if ($this->remboursements->contains($remboursement)) {
            $this->remboursements->removeElement($remboursement);
            // set the owning side to null (unless already changed)
            if ($remboursement->getMembre() === $this) {
                $remboursement->setMembre(null);
            }
        }

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
            $ticket->setMembre($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getMembre() === $this) {
                $ticket->setMembre(null);
            }
        }

        return $this;
    }

    /**
     * @return Etablissement
     */
    public function getEtablissement(): Etablissement
    {
        return $this->etablissement;
    }

    /**
     * @param Etablissement $etablissement
     * @return Membre
     */
    public function setEtablissement(Etablissement $etablissement): Membre
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    public function getDefaut(): ?bool
    {
        return $this->defaut;
    }

    public function setDefaut(bool $defaut): self
    {
        $this->defaut = $defaut;

        return $this;
    }

    public function getGestionnaire(): ?bool
    {
        return $this->gestionnaire;
    }

    public function setGestionnaire(bool $gestionnaire): self
    {
        $this->gestionnaire = $gestionnaire;

        return $this;
    }
}
