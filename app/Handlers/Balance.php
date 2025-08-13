<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Http;
use App\Services\Store;

final class Balance
{
    public function __invoke(): void
    {
        $id = (string)($_GET['account_id'] ?? '');
        if ($id === '') {
            Http::text('0', 404);
        }
        $bal = Store::get($id);
        if ($bal === null) {
            Http::text('0', 404);
        }
        Http::text((string)$bal, 200);
    }
}

