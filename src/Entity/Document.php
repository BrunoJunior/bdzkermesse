<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $label;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Etablissement::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     */
    private $etablissement;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class, inversedBy="files")
     */
    private $directory;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="directory")
     */
    private $files;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     */
    private $linkedTo;

    /**
     * @ORM\ManyToOne(targetEntity=Activite::class, inversedBy="documents")
     */
    private $activite;

    /**
     * @ORM\ManyToOne(targetEntity=Kermesse::class, inversedBy="documents")
     */
    private $kermesse;

    /**
     * @ORM\ManyToOne(targetEntity=Membre::class, inversedBy="documents")
     */
    private $membre;

    /**
     * @ORM\ManyToOne(targetEntity=Recette::class, inversedBy="documents")
     */
    private $recette;

    /**
     * @ORM\ManyToOne(targetEntity=Remboursement::class, inversedBy="documents")
     */
    private $remboursement;

    /**
     * @ORM\ManyToOne(targetEntity=Ticket::class, inversedBy="documents")
     */
    private $ticket;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $datec;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getDirectory(): ?self
    {
        return $this->directory;
    }

    public function setDirectory(?self $directory): self
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(self $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setDirectory($this);
        }

        return $this;
    }

    public function removeFile(self $file): self
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getDirectory() === $this) {
                $file->setDirectory(null);
            }
        }

        return $this;
    }

    public function getLinkedTo(): ?self
    {
        return $this->linkedTo;
    }

    public function setLinkedTo(?self $linkedTo): self
    {
        $this->linkedTo = $linkedTo;

        return $this;
    }

    public function getActivite(): ?Activite
    {
        return $this->activite;
    }

    public function setActivite(?Activite $activite): self
    {
        $this->activite = $activite;

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

    public function getMembre(): ?Membre
    {
        return $this->membre;
    }

    public function setMembre(?Membre $membre): self
    {
        $this->membre = $membre;

        return $this;
    }

    public function getRecette(): ?Recette
    {
        return $this->recette;
    }

    public function setRecette(?Recette $recette): self
    {
        $this->recette = $recette;

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

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getDatec(): ?\DateTimeImmutable
    {
        return $this->datec;
    }

    public function setDatec(\DateTimeImmutable $datec): self
    {
        $this->datec = $datec;

        return $this;
    }
}
