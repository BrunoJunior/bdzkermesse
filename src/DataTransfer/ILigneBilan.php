<?php

namespace App\DataTransfer;

/**
 * Interface ILigneBilan
 * @package App\DataTransfer
 */
interface ILigneBilan
{
    const TYPE_NORMALE = 0;
    const TYPE_SOUS_TOTAL = 1;
    const TYPE_TOTAL = 2;

    /**
     * @return int
     */
    public function getMontantDepense(): int;

    /**
     * @return int
     */
    public function getMontantRecette(): int;

    /**
     * @return int
     */
    public function getMontantBalance(): int;

    /**
     * @return string
     */
    public function getNom(): string;

    /**
     * @return int
     */
    public function getType(): int;
}
