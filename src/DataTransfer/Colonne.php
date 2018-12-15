<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 15/12/18
 * Time: 22:25
 */

namespace App\DataTransfer;

/**
 * Class Colonne
 * @package App\DataTransfer
 */
class Colonne
{
    private $icone;
    private $nom;
    private $libelle;
    private $ordonnable;
    private $recherchable;

    /**
     * Colonne constructor.
     * @param string $nom
     * @param string $libelle
     * @param string|null $icone
     * @param bool $ordonnable
     * @param bool $recherchable
     */
    public function __construct(string $nom, string $libelle, string $icone = null, bool $ordonnable = false, bool $recherchable = false) {
        $this->nom = $nom;
        $this->libelle = $libelle;
        $this->icone = $icone;
        $this->ordonnable = $ordonnable;
        $this->recherchable = $recherchable;
    }

    /**
     * @return null|string
     */
    public function getIcone(): ?string
    {
        return $this->icone;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->libelle;
    }

    /**
     * @return bool
     */
    public function isOrdonnable(): bool
    {
        return $this->ordonnable;
    }

    /**
     * @return bool
     */
    public function isRecherchable(): bool
    {
        return $this->recherchable;
    }

}