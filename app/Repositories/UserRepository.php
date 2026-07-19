<?php

namespace App\Repositories;

use App\Core\{Database, Repository};

class UserRepository extends Repository
{
    protected static string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function findByAccountNumber(string $accountNumber): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE account_number = ? LIMIT 1');
        $stmt->execute([$accountNumber]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Powers the compliance auto-flag "new account" check (SRS BR-5). Threshold
     * is read from compliance_settings (see ComplianceSettingsRepository below),
     * not hardcoded here — this method just does the date arithmetic once given
     * a threshold in days.
     */
    public function isNewAccount(int $userId, int $thresholdDays): bool
    {
        $stmt = Database::connection()->prepare('SELECT created_at FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return false;
        }

        $createdAt = new \DateTimeImmutable($row['created_at']);
        $threshold = $createdAt->modify("+{$thresholdDays} days");

        return $threshold > new \DateTimeImmutable();
    }
}
