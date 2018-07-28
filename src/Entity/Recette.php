<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RecetteRepository")
 */
class Recette
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
    private $montant;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nombre_ticket;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $montant_ticket;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite", inversedBy="recettes")
     * @ORM\JoinColumn(name="activite_id", referencedColumnName="id", nullable=false)
     */
    private $activite;

    public function getId()
    {
        return $this->id;
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

    public function getNombreTicket(): ?int
    {
        return $this->nombre_ticket;
    }

    public function setNombreTicket(?int $nombre_ticket): self
    {
        $this->nombre_ticket = $nombre_ticket;
        return $this;
    }

    public function getMontantTicket(): ?int
    {
        return $this->montant_ticket;
    }

    public function setMontantTicket(?int $montant_ticket): self
    {
        $this->montant_ticket = $montant_ticket;
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
}
