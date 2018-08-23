<?php
/**
 * Created by PhpStorm.
 * User: bdesprez
 * Date: 17/08/18
 * Time: 16:49
 */

namespace App\Service;

use App\DataTransfer\RemboursementRow;
use App\Entity\Kermesse;
use App\Entity\Remboursement;
use App\Repository\RemboursementRepository;
use Doctrine\Common\Collections\ArrayCollection;

class RemboursementRowGenerator
{
    /**
     * @var RemboursementRepository
     */
    private $rRemboursement;

    /**
     * RemboursementRowGenerator constructor.
     * @param RemboursementRepository $rRemboursement
     */
    public function __construct(RemboursementRepository $rRemboursement)
    {
        $this->rRemboursement = $rRemboursement;
    }

    /**
     * @param Remboursement $remboursement
     * @return RemboursementRow
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function generate(Remboursement $remboursement): RemboursementRow
    {
        $row = new RemboursementRow($remboursement);
        return $row;
    }

    /**
     * @param Kermesse $kermesse
     * @return ArrayCollection|RemboursementRow[]
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    public function generateList(Kermesse $kermesse): ArrayCollection
    {
        $remboursements = $this->rRemboursement->getListePourKermesse($kermesse);
        $rows = new ArrayCollection();
        foreach ($remboursements as $remboursement) {
            $rows->add($this->generate($remboursement));
        }
        return $rows;
    }
}