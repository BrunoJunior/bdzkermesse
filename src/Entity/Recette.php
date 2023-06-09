<?php

namespace App\Entity;

use App\Helper\HFloat;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecetteRepository")
 */
class Recette extends MyEntity
{
    const LIB_REPORT_STOCK = 'Stock N+1';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $montant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nombre_ticket;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite", inversedBy="recettes")
     * @ORM\JoinColumn(name="activite_id", referencedColumnName="id", nullable=false)
     */
    private $activite;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etablissement")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etablissement;

    /**
     * @ORM\Column(type="boolean")
     */
    private $report_stock;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="recette")
     */
    private $documents;

    /**
     * Recette constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime();
        $this->documents = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMontant(): int
    {
        return $this->montant ? $this->montant : 0;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getNombreTicket(): int
    {
        return $this->nombre_ticket ? $this->nombre_ticket : 0;
    }

    public function setNombreTicket(int $nombre_ticket): self
    {
        $this->nombre_ticket = $nombre_ticket;
        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(Activite $activite): self
    {
        $this->activite = $activite;
        $this->setEtablissement($activite->getEtablissement());
        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        if ($this->isReportStock()) {
            $libelle = static::LIB_REPORT_STOCK;
        }
        $this->libelle = $libelle;
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

    public function getEtablissement(): ?Etablissement
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Etablissement $etablissement): self
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function isReportStock(): ?bool
    {
        return $this->report_stock;
    }

    public function setReportStock(bool $report_stock): self
    {
        $this->report_stock = $report_stock;
        if ($report_stock) {
            $this->setLibelle("");
        }
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
            $document->setRecette($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getRecette() === $this) {
                $document->setRecette(null);
            }
        }

        return $this;
    }
}
