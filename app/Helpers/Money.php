<?php

namespace App\Helpers;

class Money
{
    public static function toMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }

    public static function toMajorUnits(int $minorUnits): float
    {
        return $minorUnits / 100;
    }

    public static function add(int $aMinor, int $bMinor): int
    {
        return $aMinor + $bMinor;
    }

    public static function subtract(int $aMinor, int $bMinor): int
    {
        return $aMinor - $bMinor;
    }

    public static function isGreaterThan(int $aMinor, int $bMinor): bool
    {
        return $aMinor > $bMinor;
    }

    public static function isGreaterThanOrEqual(int $aMinor, int $bMinor): bool
    {
        return $aMinor >= $bMinor;
    }

    public static function format(int $minorUnits, string $currency = 'USD'): string
    {
        $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'NGN' => '₦'];
        $symbol = $symbols[$currency] ?? $currency . ' ';

        return $symbol . number_format(self::toMajorUnits($minorUnits), 2);
    }
}
