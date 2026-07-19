<?php

namespace App\Models;

use App\Core\Model;

/**
 * The one Model in this phase with real casting logic: destination_details is
 * stored as a JSON text column (migration 018) because each of the 8 withdrawal
 * methods needs a differently-shaped set of fields. Every caller should go
 * through these two helpers rather than json_encode/decode inline — that's how
 * a future change to the storage format stays a one-file change.
 */
class WithdrawalRequest extends Model
{
    protected static string $table = 'withdrawal_requests';

    public static function decodeDestination(array $row): array
    {
        if (empty($row['destination_details'])) {
            return [];
        }

        $decoded = json_decode($row['destination_details'], true);

        return is_array($decoded) ? $decoded : [];
    }

    public static function encodeDestination(array $details): string
    {
        return json_encode($details);
    }
}
