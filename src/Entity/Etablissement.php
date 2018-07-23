<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EtablissementRepository")
 */
class Etablissement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $motdepasse;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Kermesse", mappedBy="etablissement_id", orphanRemoval=true)
     */
    private $kermesses;

    public function __construct()
    {
        $this->kermesses = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

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

    public function getMotdepasse(): ?string
    {
        return $this->motdepasse;
    }

    public function setMotdepasse(string $motdepasse): self
    {
        $this->motdepasse = $motdepasse;

        return $this;
    }

    /**
     * @return Collection|Kermesse[]
     */
    public function getKermesses(): Collection
    {
        return $this->kermesses;
    }

    public function addKermess(Kermesse $kermess): self
    {
        if (!$this->kermesses->contains($kermess)) {
            $this->kermesses[] = $kermess;
            $kermess->setEtablissementId($this);
        }

        return $this;
    }

    public function removeKermess(Kermesse $kermess): self
    {
        if ($this->kermesses->contains($kermess)) {
            $this->kermesses->removeElement($kermess);
            // set the owning side to null (unless already changed)
            if ($kermess->getEtablissementId() === $this) {
                $kermess->setEtablissementId(null);
            }
        }

        return $this;
    }
}
