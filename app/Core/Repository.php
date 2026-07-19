<?php

namespace App\Core;

use App\Interfaces\RepositoryInterface;

/**
 * Shared base for the find()/all() half of RepositoryInterface — every one of the
 * 16 domain repositories below needs the identical two methods. Each extends this
 * and adds its own real logic; everything beyond find()/all() is where a
 * repository actually earns its place per the SADD's division of labor.
 */
abstract class Repository implements RepositoryInterface
{
    protected static string $table = '';

    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM ' . static::$table . ' WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function all(): array
    {
        return Database::connection()->query('SELECT * FROM ' . static::$table)->fetchAll();
    }
}
