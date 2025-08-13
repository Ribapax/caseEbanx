<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Http;
use App\Services\Store;

final class Event
{
    public function __invoke(): void
    {
        $p = Http::readJson();
        $type = $p['type'] ?? '';

        if ($type === 'deposit') {
            $destination = (string)($p['destination'] ?? '');
            $amount      = (int)($p['amount'] ?? 0);
            if ($destination === '' || $amount <= 0) {
                Http::text('0', 404);
            }
            $new = Store::inc($destination, $amount);
            Http::json(['destination' => ['id' => $destination, 'balance' => $new]], 201);
        }

        if ($type === 'withdraw') {
            $origin = (string)($p['origin'] ?? '');
            $amount = (int)($p['amount'] ?? 0);
            $bal    = Store::get($origin);
            if ($origin === '' || $amount <= 0 || $bal === null) {
                Http::text('0', 404);
            }
            $new = $bal - $amount;
            Store::set($origin, $new);
            Http::json(['origin' => ['id' => $origin, 'balance' => $new]], 201);
        }

        if ($type === 'transfer') {
            $origin      = (string)($p['origin'] ?? '');
            $destination = (string)($p['destination'] ?? '');
            $amount      = (int)($p['amount'] ?? 0);

            $balOrigin = Store::get($origin);
            if ($origin === '' || $destination === '' || $amount <= 0 || $balOrigin === null) {
                Http::text('0', 404);
            }

            $newOrigin = $balOrigin - $amount;
            Store::set($origin, $newOrigin);
            $newDest = Store::inc($destination, $amount);

            Http::json([
                'origin'      => ['id' => $origin, 'balance' => $newOrigin],
                'destination' => ['id' => $destination, 'balance' => $newDest],
            ], 201);
        }

        // tipo inválido
        Http::text('0', 404);
    }
}

