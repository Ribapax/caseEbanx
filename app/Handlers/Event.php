<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Http;
use App\Application\AccountService;

final class Event
{
    public function __construct(private AccountService $svc) {}

    public function __invoke(): void
    {
        $p = Http::readJson();
        $type = $p['type'] ?? '';

        if ($type === 'deposit') {
            $dest = (string)($p['destination'] ?? '');
            $amt  = (int)($p['amount'] ?? 0);
            if ($dest === '' || $amt <= 0) Http::text('0', 404);

            $acc = $this->svc->deposit($dest, $amt);
            Http::json(['destination' => ['id' => $acc->id(), 'balance' => $acc->balance()]], 201);
        }

        if ($type === 'withdraw') {
            $origin = (string)($p['origin'] ?? '');
            $amt    = (int)($p['amount'] ?? 0);
            if ($origin === '' || $amt <= 0) Http::text('0', 404);

            $acc = $this->svc->withdraw($origin, $amt);
            if (!$acc) Http::text('0', 404);

            Http::json(['origin' => ['id' => $acc->id(), 'balance' => $acc->balance()]], 201);
        }

        if ($type === 'transfer') {
            $origin = (string)($p['origin'] ?? '');
            $dest   = (string)($p['destination'] ?? '');
            $amt    = (int)($p['amount'] ?? 0);
            if ($origin === '' || $dest === '' || $amt <= 0) Http::text('0', 404);

            $pair = $this->svc->transfer($origin, $dest, $amt);
            if (!$pair) Http::text('0', 404);

            [$o, $d] = $pair;
            Http::json([
                'origin'      => ['id' => $o->id(), 'balance' => $o->balance()],
                'destination' => ['id' => $d->id(), 'balance' => $d->balance()],
            ], 201);
        }

        Http::text('0', 404);
    }
}

