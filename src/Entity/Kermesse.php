<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\KermesseRepository")
 * @UniqueEntity(fields={"annee", "etablissement"})
 */
class Kermesse extends MyEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $annee;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etablissement", inversedBy="kermesses")
     * @ORM\JoinColumn(name="etablissement_id", referencedColumnName="id", nullable=false)
     */
    private $etablissement;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $theme;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activite", mappedBy="kermesse")
     * @ORM\OrderBy({"nom" = "ASC"})
     */
    private $activites;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Membre", inversedBy="kermesses")
     * @ORM\OrderBy({"email" = "ASC"})
     */
    private $membres;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ticket", mappedBy="kermesse", orphanRemoval=true)
     */
    private $tickets;

    /**
     * @var int Montant en centimes
     * @ORM\Column(type="integer", nullable=true)
     */
    private $montant_ticket;

    /**
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    private $dupliquee = false;

    /**
     * @ORM\Column(type="dateinterval", nullable=true)
     */
    private $dureeCreneau;

    public function __construct()
    {
        $this->activites = new ArrayCollection();
        $this->membres = new ArrayCollection();
        $this->tickets = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): self
    {
        $this->annee = $annee;
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

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * @return Collection|Activite[]
     */
    public function getActivites(): Collection
    {
        return $this->activites;
    }

    public function addActivite(Activite $activite): self
    {
        if (!$this->activites->contains($activite)) {
            $this->activites[] = $activite;
            $activite->setKermesse($this);
        }
        return $this;
    }

    public function removeActivite(Activite $activite): self
    {
        if ($this->activites->contains($activite)) {
            $this->activites->removeElement($activite);
            // set the owning side to null (unless already changed)
            if ($activite->getKermesse() === $this) {
                $activite->setKermesse(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Membre[]
     */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function addMembre(Membre $membre): self
    {
        if (!$this->membres->contains($membre)) {
            $this->membres[] = $membre;
            $membre->addKermesse($this);
        }
        return $this;
    }

    public function removeMembre(Membre $membre): self
    {
        if ($this->membres->contains($membre)) {
            $this->membres->removeElement($membre);
            $membre->removeKermesse($this);
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
            $ticket->setKermesse($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->contains($ticket)) {
            $this->tickets->removeElement($ticket);
            // set the owning side to null (unless already changed)
            if ($ticket->getKermesse() === $this) {
                $ticket->setKermesse(null);
            }
        }
        return $this;
    }

    /**
     * @return integer
     */
    public function getMontantTicket(): int
    {
        return $this->montant_ticket ? $this->montant_ticket : 0;
    }

    /**
     * @param int $montant_ticket
     * @return Kermesse
     */
    public function setMontantTicket(int $montant_ticket)
    {
        $this->montant_ticket = $montant_ticket;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isDupliquee(): bool
    {
        return $this->dupliquee;
    }

    /**
     * @param bool $dupliquee
     * @return Kermesse
     */
    public function setDupliquee(bool $dupliquee): self
    {
        $this->dupliquee = $dupliquee;
        return $this;
    }

    public function getDureeCreneau(): ?\DateInterval
    {
        return $this->dureeCreneau;
    }

    public function setDureeCreneau(?\DateInterval $dureeCreneau): self
    {
        $this->dureeCreneau = $dureeCreneau;

        return $this;
    }
}
