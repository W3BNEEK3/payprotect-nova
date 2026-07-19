<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class FxRateRepository extends Repository
{
    protected static string $table = 'fx_reference_rates';

    public function rateFor(string $currencyCode): ?float
    {
        $stmt = Database::connection()->prepare(
            'SELECT rate_to_usd FROM fx_reference_rates WHERE currency_code = ? LIMIT 1'
        );
        $stmt->execute([$currencyCode]);
        $row = $stmt->fetch();

        return $row === false ? null : (float) $row['rate_to_usd'];
    }

    public function toUsd(float $amount, string $currencyCode): float
    {
        if ($currencyCode === 'USD') {
            return $amount;
        }

        $rate = $this->rateFor($currencyCode);

        if ($rate === null) {
            throw new \RuntimeException("No FX rate on file for currency: {$currencyCode}");
        }

        return $amount * $rate;
    }
}
