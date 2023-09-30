<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 23/08/2018
 * Time: 14:26
 */

namespace App\Enum;

use SimpleEnum\Enum;

/**
 * Class RemboursementModeEnum
 * @package App\Enum
 */
class RemboursementModeEnum extends Enum
{
    const VIREMENT = 0;
    const CHEQUE = 1;
    const MONETAIRE = 2;

    /**
     * Clé / libellé
     */
    protected static function defineList(): void
    {
        static::addEnum(static::VIREMENT, 'Virement');
        static::addEnum(static::CHEQUE, 'Chèque');
        static::addEnum(static::MONETAIRE, 'Monétaire');
    }
}