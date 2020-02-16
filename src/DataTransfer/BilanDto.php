<?php


namespace App\DataTransfer;

/**
 * Class BilanDto
 * @package App\DataTransfer
 */
class BilanDto
{
    /**
     * @var array|ILigneBilan[]
     */
    private $lignes = [];

    /**
     * @var ILigneBilan
     */
    private $total;

    /**
     * @var ILigneBilan
     */
    private $lastSousTotal;

    /**
     * BilanDto constructor.
     */
    public function __construct()
    {
        $this->total = new LigneBilanSimple('Total', ILigneBilan::TYPE_TOTAL);
        $this->lastSousTotal = new LigneBilanSimple('Sous-total', ILigneBilan::TYPE_SOUS_TOTAL);
    }

    /**
     * @param ILigneBilan $ligne
     * @return $this
     */
    public function addLigne(ILigneBilan $ligne): self
    {
        if ($ligne->getMontantDepense() || $ligne->getMontantRecette()) {
            $this->lignes[] = $ligne;
            $this->total->merger($ligne);
            $this->lastSousTotal->merger($ligne);
        }
        return $this;
    }

    /**
     * @param string $nom
     * @return $this
     */
    public function addSousTotal(string $nom): self
    {
        $this->lignes[] = $this->lastSousTotal->setNom("Sous-total $nom");
        $this->lastSousTotal = new LigneBilanSimple('Sous-total', ILigneBilan::TYPE_SOUS_TOTAL);
        return $this;
    }

    /**
     * @return array|ILigneBilan[]
     */
    public function getLignes(): array
    {
        return array_merge($this->lignes, [$this->total]);
    }
}
