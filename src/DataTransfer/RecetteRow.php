<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 14:23
 */

namespace App\DataTransfer;

use App\Entity\Recette;
use App\Helper\HFloat;

class RecetteRow
{
    /**
     * @var Recette
     */
    private $recette;

    /**
     * @var string
     */
    private $actvite;

    /**
     * RecetteRow constructor.
     * @param Recette $recette
     * @param string $activite
     */
    public function __construct(Recette $recette, string $activite)
    {
        $this->recette = $recette;
        $this->actvite = $activite;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->recette->getDate()->format('d/m/Y');
    }

    /**
     * @return string
     */
    public function getActivite(): string
    {
        return $this->actvite;
    }

    /**
     * @return string
     */
    public function getLibelle(): string
    {
        return $this->recette->getLibelle();
    }

    /**
     * @return int
     */
    public function getNombreTickets(): string
    {
        return $this->recette->getNombreTicket();
    }

    /**
     * @return string
     */
    public function getMontant(): string
    {
        return HFloat::getInstance($this->recette->getMontant() / 100.0)->getMontantFormatFrancais();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->recette->getId();
    }
}