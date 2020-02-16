<?php

namespace App\DataTransfer;

/**
 * Class LigneBilanSimple
 * @package App\DataTransfer
 */
class LigneBilanSimple implements ILigneBilan
{

    /**
     * @var int
     */
    private $depenses = 0;

    /**
     * @var int
     */
    private $recettes = 0;

    /**
     * @var string
     */
    private $nom;

    /**
     * @var int
     */
    private $type = self::TYPE_NORMALE;

    /**
     * LigneBilanSimple constructor.
     * @param string $nom
     * @param int $type
     * @param int $depenses
     * @param int $recettes
     */
    public function __construct(string $nom, int $type = self::TYPE_NORMALE, int $depenses = 0, int $recettes = 0)
    {
        $this->nom = $nom;
        $this->depenses = $depenses;
        $this->recettes = $recettes;
        $this->type = $type;
    }

    /**
     * @param ILigneBilan $ligneBilan
     * @return $this
     */
    public function merger(ILigneBilan $ligneBilan): self
    {
        $this->recettes += $ligneBilan->getMontantRecette() ?: 0;
        $this->depenses += $ligneBilan->getMontantDepense() ?: 0;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMontantDepense(): int
    {
        return $this->depenses;
    }

    /**
     * @inheritDoc
     */
    public function getMontantRecette(): int
    {
        return $this->recettes;
    }

    /**
     * @inheritDoc
     */
    public function getMontantBalance(): int
    {
        return $this->recettes - $this->depenses;
    }

    /**
     * @inheritDoc
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return $this
     */
    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType(): int
    {
        return $this->type;
    }
}
