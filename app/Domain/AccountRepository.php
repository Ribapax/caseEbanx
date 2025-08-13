<?php
declare(strict_types=1);

namespace App\Domain;

interface AccountRepository
{
    public function find(string $id): ?Account;
    public function save(Account $account): void;
    public function reset(): void;
}

