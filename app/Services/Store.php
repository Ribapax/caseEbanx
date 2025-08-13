<?php
declare(strict_types=1);

namespace App\Services;

final class Store
{

    public static function read(): array
    {
        $path = sys_get_temp_dir() . '/bank_store.json';
        if (!is_file($path)) return [];
        $json = @file_get_contents($path) ?: '';
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    public static function write(array $accounts): void
    {
        $path = sys_get_temp_dir() . '/bank_store.json';
        @file_put_contents($path, json_encode($accounts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public static function reset(): void
    {
        @unlink(sys_get_temp_dir() . '/bank_store.json');
    }

    public static function get(string $id): ?int
    {
        $a = self::read();
        return array_key_exists($id, $a) ? (int)$a[$id] : null;
    }

    public static function set(string $id, int $value): void
    {
        $a = self::read();
        $a[$id] = $value;
        self::write($a);
    }

    public static function inc(string $id, int $delta): int
    {
        $a = self::read();
        $a[$id] = (int)($a[$id] ?? 0) + $delta;
        self::write($a);
        return (int)$a[$id];
    }
}

