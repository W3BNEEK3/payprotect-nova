<?php

require __DIR__ . '/../bootstrap/app.php';

use App\Core\MigrationRunner;
use App\Core\SeedRunner;

$command = $argv[1] ?? 'migrate';

match ($command) {
    'migrate'        => (new MigrationRunner())->run(),
    'migrate:status' => (new MigrationRunner())->status(),
    'seed'           => (new SeedRunner())->run($argv[2] ?? null),
    default          => print("Unknown command: {$command}\n"),
};
