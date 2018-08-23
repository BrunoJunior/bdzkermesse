<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 21/08/2018
 * Time: 23:38
 */

namespace App\DataTransfer;


use App\Entity\Membre;
use App\Entity\Remboursement;
use App\Helper\HFloat;

class MembreRow
{
    /**
     * @var Membre
     */
    private $membre;

    /**
     * @var int
     */
    private $montantNonRembourse = 0;

    /**
     * @var int
     */
    private $montantAttenteRemboursement = 0;

    /**
     * @var Remboursement
     */
    private $pemierRemboursementAttente;

    /**
     * MembreRow constructor.
     * @param Membre $membre
     */
    public function __construct(Membre $membre)
    {
        $this->membre = $membre;
    }

    /**
     * @param Membre $membre
     * @return MembreRow
     */
    public static function getInstance(Membre $membre):MembreRow
    {
        return new static($membre);
    }

    /**
     * @param int|null $montant
     * @return MembreRow
     */
    public function setMontantNonRembourse(?int $montant):self
    {
        $this->montantNonRembourse = $montant ?? 0;
        return $this;
    }

    /**
     * @param Remboursement|null $pemierRemboursementAttente
     * @return MembreRow
     */
    public function setPremierRemboursementEnAttente(?Remboursement $pemierRemboursementAttente):self
    {
        $this->pemierRemboursementAttente = $pemierRemboursementAttente;
        return $this;
    }

    /**
     * @param int|null $montantAttenteRemboursement
     * @return MembreRow
     */
    public function setMontantAttenteRemboursement(?int $montantAttenteRemboursement): self
    {
        $this->montantAttenteRemboursement = $montantAttenteRemboursement ?? 0;
        return $this;
    }

    /**
     * @return int
     */
    public function getId():int
    {
        return $this->membre->getId();
    }

    /**
     * @return string
     */
    public function getIdentite():string
    {
        return $this->membre->getPrenom() . ' ' . $this->membre->getNom();
    }

    /**
     * @return string
     */
    public function getEmail():string
    {
        return $this->membre->getEmail() ?? '';
    }

    /**
     * @return string
     */
    public function getMontantNonRembourse():string
    {
        return HFloat::getInstance($this->montantNonRembourse / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return string
     */
    public function getMontantAttenteRemboursement():string
    {
        return HFloat::getInstance($this->montantAttenteRemboursement / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return bool
     */
    public function isARembourser():bool
    {
        return $this->montantNonRembourse > 0 && !$this->isDefaut();
    }

    /**
     * @return bool
     */
    public function isEnAttenteRemboursement():bool
    {
        return $this->montantAttenteRemboursement > 0 && !$this->isDefaut();
    }

    /**
     * @return int|null
     */
    public function getIdPremierRemboursementAttente():?int
    {
        return $this->pemierRemboursementAttente ? $this->pemierRemboursementAttente->getId() : null;
    }

    /**
     * @return bool
     */
    public function isDefaut():bool
    {
        return $this->membre->getDefaut() ?? false;
    }
}