<?php

namespace App\Core;

class SeedRunner
{
    private string $seedersPath;

    public function __construct()
    {
        $this->seedersPath = __DIR__ . '/../../database/seeders';
    }

    public function run(?string $seederClass = null): void
    {
        $seeders = $seederClass !== null
            ? [$seederClass]
            : $this->allSeederClasses();

        if (empty($seeders)) {
            echo "No seeders found in {$this->seedersPath}\n";
            return;
        }

        foreach ($seeders as $class) {
            $fqcn = "Database\\Seeders\\{$class}";

            if (!class_exists($fqcn)) {
                echo "Seeder not found: {$fqcn}\n";
                continue;
            }

            echo "Seeding: {$class}...\n";
            (new $fqcn())->run();
            echo "Seeded: {$class}\n";
        }
    }

    private function allSeederClasses(): array
    {
        $files = glob($this->seedersPath . '/*.php');
        return array_map(fn($f) => basename($f, '.php'), $files);
    }
}
