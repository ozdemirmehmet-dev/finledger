<?php

namespace App\Adapters\Tax\Contracts;

interface TaxCalculatorInterface
{
    public function calculate(float $amount, string $category): float;

    public function getRate(string $category): float;

    public function getSupportedRates(): array;
}
