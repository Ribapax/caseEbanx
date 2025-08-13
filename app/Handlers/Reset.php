<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Http;
use App\Services\Store;

final class Reset
{
    public function __invoke(): void
    {
        Store::reset();
        Http::text('OK', 200);
    }
}

