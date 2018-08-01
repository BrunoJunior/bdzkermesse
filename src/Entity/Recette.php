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
     * Montant global d'une recette
     * Montant en centimes + nombre de tickets * le montant d'un ticket
     * @return int
     */
    public function getMontantGlobal(): int
    {
        $montant = $this->getMontant();
        if ($this->getActivite() !== null && $this->getActivite()->getKermesse() !== null) {
            $montantTicket = $this->getActivite()->getKermesse()->getMontantTicket();
            $montant += ($this->getNombreTicket() * $montantTicket);
        }
        return $montant;
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
}
