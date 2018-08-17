<?php

namespace App\Entity;

use App\Helper\HFloat;
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
        return $this;
    }

    /**
     * Le montant en euro au format français
     * @return string
     */
    public function getMontantEuro(): string
    {
        $montant = $this->montant ? $this->montant / 100 : 0.0;
        return HFloat::getInstance($montant)->getMontantFormatFrancais();
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
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
}
