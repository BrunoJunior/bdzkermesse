<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 19/03/2019
 * Time: 17:30
 */

namespace App\DataTransfer;


use DateTimeInterface;

class LigneComptable
{
    /**
     * @var DateTimeInterface
     */
    private $date;
    /**
     * @var string
     */
    private $libelle;
    /**
     * En centimes
     * @var integer
     */
    private $debit = 0;
    /**
     * En centimes
     * @var integer
     */
    private $credit = 0;

    /**
     * LigneComptable constructor.
     * @param DateTime $date
     * @param string $libelle
     * @param int $montant
     */
    public function __construct(DateTimeInterface $date, string $libelle, int $montant)
    {
        $this->date = $date;
        $this->libelle = $libelle;
        if ($montant > 0) {
            $this->credit = $montant;
        } elseif ($montant < 0) {
            $this->debit = -$montant;
        }
    }

    /**
     * Transforme un montant (en centime) en chaîne de caractères
     * @param int $montant
     * @return string
     */
    private static function montantToStr(int $montant): string
    {
        if ($montant === 0) {
            return '';
        }
        $chaine = '' . floor($montant / 100);
        $cents = $montant % 100;
        if ($cents > 0) {
            $chaine .= ',' . sprintf("%'.02d", $cents);
        }
        return $chaine;
    }

    /**
     * Comparaison de deux lignes comptables
     * C'est la date qui détermine l'ordre
     * @param LigneComptable $ligne
     * @return int
     */
    public function comparerAvec(LigneComptable $ligne): int
    {
        return $this->date <=> $ligne->date;
    }

    /**
     * Transformation au format pour export CSV
     * @return array
     */
    public function formatToCSV(): array
    {
        return [$this->date->format('d/m/Y'), $this->libelle, static::montantToStr($this->debit), static::montantToStr($this->credit)];
    }
}