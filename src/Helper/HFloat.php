<?php
/**
 * Created by PhpStorm.
 * User: bruno
 * Date: 29/07/2018
 * Time: 22:25
 */

namespace App\Helper;


class HFloat
{
    /**
     * @var float
     */
    private $number;

    /**
     * HFloat constructor.
     * @param float $number
     */
    public function __construct(float $number)
    {
        $this->number = $number;
    }

    /**
     * @param float $number
     * @return HFloat
     */
    public static function getInstance(float $number)
    {
        return new static($number);
    }

    /**
     * Le flottant en euro au format français
     * @return string
     */
    public function getMontantFormatFrancais(): string
    {
        return number_format($this->number, 2, ',', '.') . ' €';
    }
}