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

    public function findByResetToken(string $token): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE reset_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    public function updatePassword(int $userId, string $hashedPassword): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password = ?, reset_token = NULL WHERE id = ?');
        $stmt->execute([$hashedPassword, $userId]);
    }

    public function setResetToken(int $userId, string $token): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET reset_token = ? WHERE id = ?');
        $stmt->execute([$token, $userId]);
    }

    public function generateUniqueAccountNumber(): string
    {
        do {
            $number = (string) random_int(1000000000, 9999999999);
            $existing = $this->findByAccountNumber($number);
        } while ($existing !== null);
        return $number;
    }

    public function isFullyVerified(int $userId): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT is_kyc_verified, is_upgraded, is_upgrade_verified FROM users WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return false;
        }

        return (bool)$row['is_kyc_verified'] && (bool)$row['is_upgraded'] && (bool)$row['is_upgrade_verified'];
    }

    public function refundBalance(int $userId, float $amount): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET balance = balance + ? WHERE id = ?'
        );
        $stmt->execute([$amount, $userId]);
    }

    public function deductBalance(int $userId, float $amount): void
    {
        $stmt = Database::connection()->prepare(
            'UPDATE users SET balance = balance - ? WHERE id = ?'
        );
        $stmt->execute([$amount, $userId]);
    }
}
