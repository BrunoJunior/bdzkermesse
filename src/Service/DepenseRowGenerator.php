<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 16:49
 */

namespace App\Service;

use App\DataTransfer\DepenseRow;
use App\Entity\Activite;
use App\Entity\Depense;
use App\Repository\DepenseRepository;
use Doctrine\Common\Collections\ArrayCollection;

class DepenseRowGenerator
{
    /**
     * @var DepenseRepository
     */
    private $rDepense;

    /**
     * DepenseRowGenerator constructor.
     * @param DepenseRepository $rDepense
     */
    public function __construct(DepenseRepository $rDepense)
    {
        $this->rDepense = $rDepense;
    }

    /**
     * @param Depense $depense
     * @return DepenseRow
     */
    public function generate(Depense $depense): DepenseRow
    {
        $row = new DepenseRow($depense);
        return $row;
    }

    /**
     * @param Activite $activite
     * @return ArrayCollection|DepenseRow[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function generateList(Activite $activite): ArrayCollection
    {
        $depenses = $this->rDepense->findByActivite($activite);
        $rows = new ArrayCollection();
        foreach ($depenses as $depense) {
            $rows->add($this->generate($depense));
        }
        return $rows;
    }
}