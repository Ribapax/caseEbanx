<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\{Account, AccountRepository};

final class AccountService
{
    public function __construct(private AccountRepository $repo) {}

    public function reset(): void
    {
        $this->repo->reset();
    }

    public function getBalance(string $id): ?int
    {
        $acc = $this->repo->find($id);
        return $acc?->balance();
    }

    public function deposit(string $destination, int $amount): Account
    {
        $acc = $this->repo->find($destination) ?? new Account($destination, 0);
        $acc->deposit($amount);
        $this->repo->save($acc);
        return $acc;
    }

    /** retorna null se origin não existe */
    public function withdraw(string $origin, int $amount): ?Account
    {
        $acc = $this->repo->find($origin);
        if (!$acc) return null;
        $acc->withdraw($amount);
        $this->repo->save($acc);
        return $acc;
    }

    /** retorna [origin, destination] ou null se origin não existe */
    // Não realizei a validação de saldo na conta de origem
    public function transfer(string $origin, string $destination, int $amount): ?array
    {
        $o = $this->repo->find($origin);
        if (!$o) return null;

        $d = $this->repo->find($destination) ?? new Account($destination, 0);

        $o->withdraw($amount);
        $d->deposit($amount);

        $this->repo->save($o);
        $this->repo->save($d);

        return [$o, $d];
    }

    private function getOrCreate(string $id): Account
    {
        return $this->repo->find($id) ?? new Account($id, 0);
    }

    private function getExisting(string $id): ?Account
    {
        return $this->repo->find($id);
    }
}
