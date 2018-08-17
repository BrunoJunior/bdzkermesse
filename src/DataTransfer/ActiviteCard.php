<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 16/08/2018
 * Time: 15:49
 */

namespace App\DataTransfer;
use App\Entity\Activite;
use App\Helper\HFloat;

/**
 * Class ActiviteCard
 * @package App\DataTransfer
 */
class ActiviteCard
{

    /**
     * @var Activite
     */
    private $activite;

    /**
     * @var ?int
     */
    private $recette;

    /**
     * @var int
     */
    private $depense = 0;

    /**
     * @var ?int
     */
    private $nombreTickets;

    /**
     * ActiviteCard constructor.
     * @param Activite $activite
     */
    public function __construct(Activite $activite)
    {
        $this->activite = $activite;
    }

    /**
     * @param int $recette
     * @return ActiviteCard
     */
    public function setRecette(int $recette): ActiviteCard
    {
        if ($this->activite->isAccepteMonnaie()) {
            $this->recette = $recette;
        }
        return $this;
    }

    /**
     * @param int $depense
     * @return ActiviteCard
     */
    public function setDepense(int $depense): ActiviteCard
    {
        $this->depense = $depense;
        return $this;
    }

    /**
     * @param int $nombreTickets
     * @return ActiviteCard
     */
    public function setNombreTickets(int $nombreTickets): ActiviteCard
    {
        if ($this->activite->isAccepteTickets()) {
            $this->nombreTickets = $nombreTickets;
        }
        return $this;
    }

    /**
     * Id de l'activité liée
     * @return int
     */
    public function getId(): int
    {
        return $this->activite->getId();
    }

    /**
     * Titre de la carte (le nom de l'activité)
     * @return string
     */
    public function getTitre(): string
    {
        return $this->activite->getNom();
    }

    /**
     * La recette sous forme de HTML
     * @return null|string
     */
    public function getRecette(): string
    {
        return $this->recette === null ? '' : HFloat::getInstance($this->recette / 100.00)->getMontantFormatFrancais();
    }

    /**
     * Dépense sous forme HTML
     * @return string
     */
    public function getDepense(): string
    {
        return HFloat::getInstance($this->depense / 100.00)->getMontantFormatFrancais();
    }

    /**
     * Le nombre de tickets sous forme HTML
     * @return string
     */
    public function getNombreTickets(): string
    {
        $montantTicket = $this->activite->getKermesse()->getMontantTicket();
        if ($montantTicket === 0 || !$this->activite->isAccepteTickets()) {
            return '';
        }
        return $this->nombreTickets . '(x'.HFloat::getInstance($montantTicket / 100.00)->getMontantFormatFrancais().')';
    }

    /**
     * Le total sous forme de HTML
     * Recette - Dépenses + (Nombres de tickets * Montant ticket)
     * @return string
     */
    public function getTotal(): string
    {
        $total = ($this->recette ?? 0) - $this->depense + (($this->nombreTickets ?? 0) * $this->activite->getKermesse()->getMontantTicket());
        return HFloat::getInstance($total / 100.00)->getMontantFormatFrancais();
    }

    /**
     * @return Activite
     */
    public function getActivite(): Activite
    {
        return $this->activite;
    }

}