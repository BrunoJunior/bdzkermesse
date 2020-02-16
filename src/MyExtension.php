<?php

namespace App;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MyExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('priceFromCents', [$this, 'formatPriceFromCents']),
        ];
    }

    public function formatPriceFromCents($cents, $currency = '€', $decimals = 2, $decPoint = ',', $thousandsSep = '')
    {
        $price = number_format($cents / 100, $decimals, $decPoint, $thousandsSep);
        return "$price $currency";
    }
}
