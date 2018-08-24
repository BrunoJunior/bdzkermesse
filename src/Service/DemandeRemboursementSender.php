<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 24/08/2018
 * Time: 14:37
 */

namespace App\Service;

/**
 * Class DemandeRemboursementSender
 * @package App\Service
 */
class DemandeRemboursementSender extends AbstractSender
{

    /**
     * Sans les extensions
     * @return string
     */
    protected function getTemplate(): string
    {
        return "remboursement_demande";
    }
}