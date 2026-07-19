<?php
/**
 * One-time backfill: encrypts existing plaintext card_number/cvv values into the
 * encrypted columns added by migration 017. Safe to run more than once — only
 * touches rows where the encrypted columns are still NULL, so a second run finds
 * nothing left to do.
 */
require __DIR__ . '/../vendor/autoload.php';

use App\Core\{EnvLoader, Database};
use App\Repositories\VirtualCardRepository;
use App\Helpers\Crypto;

EnvLoader::load(__DIR__ . '/../.env');

$repo = new VirtualCardRepository();
$rows = $repo->findNeedingEncryptionBackfill();

echo "Found " . count($rows) . " card(s) needing backfill.\n";

$pdo = Database::connection();
$updated = 0;

foreach ($rows as $row) {
    $encryptedNumber = Crypto::encrypt($row['card_number']);
    $encryptedCvv = Crypto::encrypt($row['cvv']);

    $stmt = $pdo->prepare('UPDATE virtual_cards SET card_number_encrypted = ?, cvv_encrypted = ? WHERE id = ?');
    $stmt->execute([$encryptedNumber, $encryptedCvv, $row['id']]);
    $updated++;
}

echo "Backfilled {$updated} card(s).\n";
