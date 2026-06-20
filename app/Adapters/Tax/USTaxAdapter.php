<?php

namespace App\Adapters\Tax;

use App\Adapters\Tax\Contracts\TaxCalculatorInterface;
use InvalidArgumentException;

class USTaxAdapter implements TaxCalculatorInterface
{
    private const RATES = [
        'standard' => 0.085,
    ];

    public function calculate(float $amount, string $category): float
    {
        return round($amount * $this->getRate($category), 2);
    }

    public function getRate(string $category): float
    {
        if (! array_key_exists($category, self::RATES)) {
            throw new InvalidArgumentException("Unsupported sales tax category: {$category}");
        }

        return self::RATES[$category];
    }

    public function getSupportedRates(): array
    {
        return self::RATES;
    }
}
