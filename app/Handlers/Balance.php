<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Http;
use App\Application\AccountService;

final class Balance
{
    public function __construct(private AccountService $svc) {}

    public function __invoke(): void
    {
        $id = (string)($_GET['account_id'] ?? '');
        if ($id === '') Http::text('0', 404);

        $bal = $this->svc->getBalance($id);
        if ($bal === null) Http::text('0', 404);

        Http::text((string)$bal, 200);
    }
}

