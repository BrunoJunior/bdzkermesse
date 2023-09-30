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
     * @param array|null $typesAutorises
     * @return array|ILigneBilan[]
     */
    public function getLignes(?array $typesAutorises = null): array
    {
        $lignes = array_merge($this->lignes, [$this->total]);
        if ($typesAutorises === null) {
            return $lignes;
        }
        return array_filter($lignes, function (ILigneBilan $ligne) use ($typesAutorises) {
            return in_array($ligne->getType(), $typesAutorises);
        });
    }
}
