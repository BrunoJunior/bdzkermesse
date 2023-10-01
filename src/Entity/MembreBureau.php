<?php

namespace App\Entity;

use App\Repository\MembreBureauRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MembreBureauRepository::class)
 */
class MembreBureau
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Membre::class, inversedBy="membres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $membre;

    /**
     * @ORM\ManyToOne(targetEntity=Bureau::class, inversedBy="membres")
     * @ORM\JoinColumn(nullable=false)
     */
    private $bureau;

    /**
     * @ORM\Column(type="enumrole")
     */
    private $role;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBureau(): ?Bureau
    {
        return $this->bureau;
    }

    public function setBureau(?Bureau $bureau): self
    {
        $this->bureau = $bureau;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }
}
