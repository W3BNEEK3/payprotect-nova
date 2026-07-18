<?php

namespace App\Core;

use PDO;

class MigrationRunner
{
    private string $migrationsPath;

    public function __construct()
    {
        $this->migrationsPath = __DIR__ . '/../../database/migrations';
    }

    public function run(): void
    {
        $this->ensureMigrationsTable();

        $applied = $this->appliedMigrations();
        $files = $this->migrationFiles();

        foreach ($files as $file) {
            $name = basename($file);

            if (in_array($name, $applied, true)) {
                continue;
            }

            echo "Applying {$name}...\n";
            $sql = file_get_contents($file);
            Database::connection()->exec($sql);
            $this->recordMigration($name);
            echo "Applied {$name}\n";
        }

        echo "Migrations complete.\n";
    }

    public function status(): void
    {
        $this->ensureMigrationsTable();

        $applied = $this->appliedMigrations();
        $files = $this->migrationFiles();

        if (empty($files)) {
            echo "No migration files found in {$this->migrationsPath}\n";
            return;
        }

        foreach ($files as $file) {
            $name = basename($file);
            $state = in_array($name, $applied, true) ? 'applied' : 'pending';
            echo sprintf("%-50s %s\n", $name, $state);
        }
    }

    private function ensureMigrationsTable(): void
    {
        Database::connection()->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT NOT NULL AUTO_INCREMENT,
                migration VARCHAR(255) NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    private function appliedMigrations(): array
    {
        $stmt = Database::connection()->query('SELECT migration FROM migrations');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function migrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.sql');
        sort($files);
        return $files;
    }

    private function recordMigration(string $name): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO migrations (migration) VALUES (?)');
        $stmt->execute([$name]);
    }
}
