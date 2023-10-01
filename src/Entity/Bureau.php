<?php

namespace App\Entity;

use App\Repository\BureauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=BureauRepository::class)
 * @ORM\Table(
 *      name="bureau",
 *      uniqueConstraints={@ORM\UniqueConstraint(columns={"etablissement_id", "annee"})}
 * )
 * @UniqueEntity(
 *      fields={"etablissement", "annee"},
 *      errorPath="annee",
 *      message="Un bureau existe déjà pour cette année !"
 *  )
 */
class Bureau
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Etablissement::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $etablissement;

    /**
     * @ORM\Column(type="integer")
     */
    private $annee;

    /**
     * @ORM\OneToMany(targetEntity=MembreBureau::class, mappedBy="bureau", orphanRemoval=true)
     */
    private $membres;

    public function __construct()
    {
        $this->membres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): self
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * @return Collection<int, MembreBureau>
     */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function addMembre(MembreBureau $membre): self
    {
        if (!$this->membres->contains($membre)) {
            $this->membres[] = $membre;
            $membre->setBureau($this);
        }

        return $this;
    }

    public function removeMembre(MembreBureau $membre): self
    {
        if ($this->membres->removeElement($membre)) {
            // set the owning side to null (unless already changed)
            if ($membre->getBureau() === $this) {
                $membre->setBureau(null);
            }
        }

        return $this;
    }
}
