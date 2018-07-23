<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DepenseRepository")
 */
class Depense
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Ticket", inversedBy="depenses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ticket_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite", inversedBy="depenses")
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

    public function getTicketId(): ?Ticket
    {
        return $this->ticket_id;
    }

    public function setTicketId(?Ticket $ticket_id): self
    {
        $this->ticket_id = $ticket_id;

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
