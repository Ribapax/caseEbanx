<?php
declare(strict_types=1);

namespace App\Domain;

final class Account
{
    public function __construct(
        private string $id,
        private int $balance = 0
    ) {}

    public function id(): string     { return $this->id; }
    public function balance(): int   { return $this->balance; }

    public function deposit(int $amount): void   { $this->balance += $amount; }
    public function withdraw(int $amount): void  { $this->balance -= $amount; }
}

