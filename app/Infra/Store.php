<?php
declare(strict_types=1);

namespace App\Infra;

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

}

