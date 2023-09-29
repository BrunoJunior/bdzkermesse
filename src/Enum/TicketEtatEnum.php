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
 * Class TicketEtatEnum
 * @package App\Enum
 */
class TicketEtatEnum extends Enum
{
    use PastilleEnumTrait;

    const A_REMBOURSER = 0;
    const EN_ATTENTE = 1;
    const REMBOURSE = 2;

    /**
     * Définir la liste des code - libellés
     */
    protected static function defineList():void
    {
        static::addEnum(static::A_REMBOURSER, 'À rembourser')->setPastilleClasse('text-danger');
        static::addEnum(static::EN_ATTENTE, 'En attente')->setPastilleClasse('text-warning');
        static::addEnum(static::REMBOURSE, 'Remboursé')->setPastilleClasse('text-success');
    }

}