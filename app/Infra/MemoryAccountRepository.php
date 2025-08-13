<?php
declare(strict_types=1);

namespace App\Infra;

use App\Domain\{Account, AccountRepository};
use App\Infra\Store;


final class MemoryAccountRepository implements AccountRepository
{
    public function find(string $id): ?Account
    {
        $arr = Store::read();
        if (!array_key_exists($id, $arr)) return null;
        return new Account($id, (int)$arr[$id]);
    }

    public function save(Account $account): void
    {
        $arr = Store::read();
        $arr[$account->id()] = $account->balance();
        Store::write($arr);
    }

    public function reset(): void
    {
        Store::reset();
    }
}

