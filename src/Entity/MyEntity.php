<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 18/08/2018
 * Time: 16:45
 */

namespace App\Entity;


abstract class MyEntity
{
    /**
     * @return Etablissement
     */
    public abstract function getEtablissement():?Etablissement;

    /**
     * L'établissement est-il le propriétaire de la donnée ?
     * @param Etablissement $etablissement
     * @return bool
     */
    public function isProprietaire(?Etablissement $etablissement = null):bool
    {
        return $etablissement && $this->getEtablissement() === $etablissement;
    }
}