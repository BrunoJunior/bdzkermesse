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
     * @ORM\JoinColumn(nullable=false)
     */
    private $activite_id;

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

    public function getActiviteId(): ?Activite
    {
        return $this->activite_id;
    }

    public function setActiviteId(?Activite $activite_id): self
    {
        $this->activite_id = $activite_id;

        return $this;
    }
}
