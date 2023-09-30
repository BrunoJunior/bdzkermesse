<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 16/08/2018
 * Time: 15:49
 */

namespace App\DataTransfer;
use App\Entity\Activite;
use App\Entity\Creneau;
use App\Entity\InscriptionBenevole;
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
    private $recette = 0;

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
        $this->recette = $recette;
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
     * @return int
     */
    public function getMontantTicket(): int
    {
        $kermesse = $this->activite->getKermesse();
        return $kermesse  ? $kermesse->getMontantTicket() : 0;
    }

    /**
     * La recette sous forme de HTML
     * @return null|string
     */
    public function getRecette(): string
    {
        return $this->recette == 0 ? '' : HFloat::getInstance($this->recette / 100.00)->getMontantFormatFrancais();
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
        $montantTicket = $this->getMontantTicket();
        if ($montantTicket === 0 || !$this->activite->isAccepteTickets()) {
            return '';
        }
        return $this->nombreTickets . '(x'.HFloat::getInstance($montantTicket / 100.00)->getMontantFormatFrancais().')';
    }

    /**
     * @return int
     */
    public function getNombreTicketsInt(): int
    {
        return $this->nombreTickets ?: 0;
    }

    /**
     * Le total sous forme de HTML
     * Recette - Dépenses + (Nombres de tickets * Montant ticket)
     * @return int
     */
    public function getTotal(): int
    {
        return ($this->recette ?? 0) - $this->depense + (($this->nombreTickets ?? 0) * $this->getMontantTicket());
    }

    /**
     * @return int
     */
    public function getNombreBenevolesRequis(): int
    {
        return array_reduce($this->activite->getCreneaux()->getValues(), function (int $somme, Creneau $creneau) {
            return $somme + $creneau->getNbBenevolesRecquis();
        }, 0);
    }

    /**
     * @return int
     */
    public function getNombreBenevolesInscrits(): int
    {
        return array_reduce($this->activite->getCreneaux()->getValues(), function (int $somme, Creneau $creneau) {
            return $somme + $creneau->getInscriptionBenevoles()->filter(function (InscriptionBenevole $inscription) {
                    return $inscription->getValidee();
                })->count();
        }, 0);
    }

    /**
     * @return int
     */
    public function getNombreBenevolesEnAttente(): int
    {
        return array_reduce($this->activite->getCreneaux()->getValues(), function (int $somme, Creneau $creneau) {
            return $somme + $creneau->getInscriptionBenevoles()->filter(function (InscriptionBenevole $inscription) {
                return !$inscription->getValidee();
                })->count();
        }, 0);
    }

    /**
     * @return int
     */
    public function getTauxInscription(): int
    {
        return round($this->getNombreBenevolesInscrits() * 100 / $this->getNombreBenevolesRequis());
    }

    /**
     * @return int
     */
    public function getTauxInscriptionEnAttente(): int
    {
        return round($this->getNombreBenevolesEnAttente() * 100 / $this->getNombreBenevolesRequis());
    }

    /**
     * @return Activite
     */
    public function getActivite(): Activite
    {
        return $this->activite;
    }

    /**
     * Le descriptif de l'activité (avec son type)
     * @return ActiviteDescriptionModal
     */
    public function getDescription(): ActiviteDescriptionModal {
        return new ActiviteDescriptionModal($this->activite);
    }

    /**
     * Is the card sortable ?
     * @return bool
     */
    public function isSortable(): bool {
        return $this->activite->getKermesse() !== null;
    }

    /**
     * No benevoles ?
     * @return bool
     */
    public function isWithoutBenevole(): bool {
        return $this->getNombreBenevolesEnAttente() + $this->getNombreBenevolesInscrits() === 0;
    }
}
