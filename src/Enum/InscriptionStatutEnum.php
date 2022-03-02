<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 22/08/2018
 * Time: 09:23
 */

namespace App\Enum;

use SimpleEnum\Enum;

/**
 * Class InscriptionStatutEnum
 * @package App\Enum
 */
class InscriptionStatutEnum extends Enum
{
    use PastilleEnumTrait;

    const EN_ATTENTE = 0;
    const VALIDEE = 1;
    const REFUSEE = 2;

    /**
     * Définir la liste des code - libellés
     */
    protected static function defineList():void
    {
        static::addEnum(static::EN_ATTENTE, 'En attente')->setPastilleClasse('text-warning');
        static::addEnum(static::REFUSEE, 'Refusée')->setPastilleClasse('text-danger');
        static::addEnum(static::VALIDEE, 'Validée')->setPastilleClasse('text-success');
    }

}
