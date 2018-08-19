<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 16:49
 */

namespace App\Service;

use App\DataTransfer\RecetteRow;
use App\Entity\Activite;
use App\Entity\Kermesse;
use App\Entity\Recette;
use App\Repository\RecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;

class RecetteRowGenerator
{
    /**
     * @var RecetteRepository
     */
    private $rRecette;

    /**
     * RecetteRowGenerator constructor.
     * @param RecetteRepository $rRecette
     */
    public function __construct(RecetteRepository $rRecette)
    {
        $this->rRecette = $rRecette;
    }

    /**
     * @param Recette $recette
     * @param string $activite
     * @return RecetteRow
     */
    public function generate(Recette $recette): RecetteRow
    {
        return new RecetteRow($recette, $recette->getActivite()->getNom());
    }

    /**
     * @param Kermesse $kermesse
     * @return ArrayCollection|RecetteRow[]
     */
    public function generateListPourKermesse(Kermesse $kermesse): ArrayCollection
    {
        $rows = new ArrayCollection();
        foreach ($this->rRecette->findByKermesse($kermesse) as $recette) {
            $rows->add($this->generate($recette));
        }
        return $rows;
    }

    /**
     * @param Activite $activite
     * @return ArrayCollection|RecetteRow[]
     */
    public function generateListPourActivite(Activite $activite): ArrayCollection
    {
        $rows = new ArrayCollection();
        foreach ($this->rRecette->findByActivite($activite) as $recette) {
            $rows->add($this->generate($recette));
        }
        return $rows;
    }
}