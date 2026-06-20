<?php

namespace App\Adapters\Tax;

use App\Adapters\Tax\Contracts\TaxCalculatorInterface;
use InvalidArgumentException;

class UKTaxAdapter implements TaxCalculatorInterface
{
    private const RATES = [
        'standard' => 0.20,
        'reduced'  => 0.05,
    ];

    public function calculate(float $amount, string $category): float
    {
        return round($amount * $this->getRate($category), 2);
    }

    public function getRate(string $category): float
    {
        if (! array_key_exists($category, self::RATES)) {
            throw new InvalidArgumentException("Unsupported VAT category: {$category}");
        }

        return self::RATES[$category];
    }

    public function getSupportedRates(): array
    {
        return self::RATES;
    }
}
