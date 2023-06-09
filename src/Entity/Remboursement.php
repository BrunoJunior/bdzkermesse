<?php

namespace App\Entity;

use App\Enum\RemboursementEtatEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RemboursementRepository")
 */
class Remboursement extends MyEntity
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $numero_suivi;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Membre", inversedBy="remboursements")
     * @ORM\JoinColumn(name="membre_id", referencedColumnName="id", nullable=false)
     */
    private $membre;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket", mappedBy="remboursement")
     */
    private $tickets;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etablissement")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etablissement;

    /**
     * @ORM\Column(type="smallint", options={"default" : 0})
     */
    private $etat = RemboursementEtatEnum::EN_ATTENTE;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="remboursement")
     */
    private $documents;

    /**
     * Remboursement constructor.
     */
    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->date = new \DateTime();
        $this->documents = new ArrayCollection();
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

    public function setMode(?int $mode): self
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

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre): self
    {
        $this->membre = $membre;
        if ($membre instanceof Membre) {
            $this->setEtablissement($membre->getEtablissement());
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
            $ticket->setRemboursement($this);
        }
        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getRemboursement() === $this) {
                $ticket->setRemboursement(null);
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

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setRemboursement($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getRemboursement() === $this) {
                $document->setRemboursement(null);
            }
        }

        return $this;
    }
}
