<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 16/08/2018
 * Time: 15:49
 */

namespace App\DataTransfer;
use App\Entity\Kermesse;
use App\Helper\HFloat;

/**
 * Class KermesseCard
 * @package App\DataTransfer
 */
class KermesseCard
{

    /**
     * @var Kermesse
     */
    private $kermesse;

    /**
     * @var int
     */
    private $recette;

    /**
     * @var int
     */
    private $depense;

    /**
     * KermesseCard constructor.
     * @param Kermesse $kermesse
     */
    public function __construct(Kermesse $kermesse)
    {
        $this->kermesse = $kermesse;
    }

    /**
     * @return bool
     */
    public function isDerniere(): bool
    {
        return !$this->kermesse->isDupliquee();
    }

    /**
     * Montant au format HTML
     * @return null|string
     */
    public function getMontantTicket(): ?string
    {
        $montant = $this->kermesse->getMontantTicket();
        return $montant ? HFloat::getInstance($montant / 100.0)->getMontantFormatFrancais() : null;
    }

    /**
     * @param int $recette
     * @return KermesseCard
     */
    public function setRecette(int $recette): KermesseCard
    {
        $this->recette = $recette;
        return $this;
    }

    /**
     * @param int $depense
     * @return KermesseCard
     */
    public function setDepense(int $depense): KermesseCard
    {
        $this->depense = $depense;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecette(): string
    {
        return HFloat::getInstance($this->recette / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return string
     */
    public function getDepense(): string
    {
        return HFloat::getInstance($this->depense / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return string
     */
    public function getTitre(): string
    {
        return $this->kermesse->getAnnee() . ' - ' . $this->kermesse->getTheme();
    }

    /**
     * @return string
     */
    public function getBalance(): string
    {
        return HFloat::getInstance(($this->recette - $this->depense) / 100.00)->getMontantFormatFrancais();
    }

    /**
     * @return Kermesse
     */
    public function getKermesse(): Kermesse
    {
        return $this->kermesse;
    }
}