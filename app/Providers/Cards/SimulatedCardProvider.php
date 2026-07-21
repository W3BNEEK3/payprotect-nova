<?php

namespace App\Providers\Cards;

use App\Interfaces\CardIssuerInterface;
use App\Helpers\Crypto;
use App\Models\VirtualCard;

/**
 * Consolidates the two disagreeing card-issuance paths found during the SADD
 * review (admin/approve_virtual_card.php vs the orphaned user/generate_card.php)
 * into one path (P4.2). New cards write ONLY to the encrypted columns — the
 * plaintext card_number/cvv columns are left NULL going forward. Existing cards
 * with plaintext-only data get caught up by the one-time backfill script (P4.3),
 * not by this class.
 */
class SimulatedCardProvider implements CardIssuerInterface
{
    public function issue(int $userId, bool $isApproved = false): array
    {
        $cardNumber = $this->generateCardNumber();
        $expiry = $this->generateExpiry();
        $cvv = $this->generateCvv();

        VirtualCard::create([
            'user_id' => $userId,
            'card_number_encrypted' => Crypto::encrypt($cardNumber),
            'cvv_encrypted' => Crypto::encrypt($cvv),
            'expiry_date' => $expiry,
            'status' => 'active',
            'is_virtual_card_approved' => $isApproved ? 1 : 0, // still requires admin approval if not explicitly approved, FR-4.2
        ]);

        return [
            'number' => $cardNumber,
            'expiry' => $expiry,
            'cvv' => $cvv,
        ];
    }

    private function generateCardNumber(): string
    {
        // Simulated card — a Luhn-valid 16-digit PAN using a prefix that is
        // deliberately NOT a real issuer BIN range, so it can never be mistaken
        // for or accidentally processed as a genuine card.
        $prefix = '4000';
        $rest = '';

        for ($i = 0; $i < 11; $i++) {
            $rest .= random_int(0, 9);
        }

        $partial = $prefix . $rest;

        return $partial . $this->luhnCheckDigit($partial);
    }

    private function luhnCheckDigit(string $number): int
    {
        $sum = 0;
        $alternate = true;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];

            if ($alternate) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }

            $sum += $n;
            $alternate = !$alternate;
        }

        return (10 - ($sum % 10)) % 10;
    }

    private function generateExpiry(): string
    {
        return (new \DateTimeImmutable())->modify('+3 years')->format('m/y');
    }

    private function generateCvv(): string
    {
        return str_pad((string) random_int(0, 999), 3, '0', STR_PAD_LEFT);
    }
}
