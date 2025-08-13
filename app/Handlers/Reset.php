<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Http;
use App\Application\AccountService;

final class Reset
{
    public function __construct(private AccountService $svc) {}

    public function __invoke(): void
    {
        $this->svc->reset();
        Http::text('OK', 200);
    }
}

