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
 * Class RemboursementEtatEnum
 * @package App\Enum
 */
class RemboursementEtatEnum extends Enum
{
    use PastilleEnumTrait;

    const EN_ATTENTE = 0;
    const VALIDE = 1;

    /**
     * @var TicketEtatEnum
     */
    private $etatTicket;

    /**
     * Définir la liste des code - libellés
     */
    protected static function defineList():void
    {
        static::addEnum(static::EN_ATTENTE, 'En attente')->setPastilleClasse('text-warning')->setEtatTicket(TicketEtatEnum::EN_ATTENTE);
        static::addEnum(static::VALIDE, 'Remboursé')->setPastilleClasse('text-success')->setEtatTicket(TicketEtatEnum::REMBOURSE);
    }

    /**
     * @return TicketEtatEnum
     */
    public function getEtatTicket(): TicketEtatEnum
    {
        return $this->etatTicket;
    }

    /**
     * @param int $etatTicket
     * @return RemboursementEtatEnum
     * @throws \SimpleEnum\Exception\UnknownEumException
     */
    private function setEtatTicket(int $etatTicket): RemboursementEtatEnum
    {
        $this->etatTicket = TicketEtatEnum::getInstance($etatTicket);
        return $this;
    }
}