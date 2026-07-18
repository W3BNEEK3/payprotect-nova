<?php
/**
 * database/db.php — kept only because the ~60 existing flat files in admin/ and
 * user/ still `require` it directly and expect a $conn variable. New code should
 * use App\Core\Database::connection() instead. This file is retired entirely once
 * every flat file has been migrated into a Controller (tracked per-phase; see
 * Implementation Plan P19.4 for final cleanup).
 */

require_once __DIR__ . '/../bootstrap/app.php';

$conn = \App\Core\Database::connection();
