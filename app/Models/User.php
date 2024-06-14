<?php

namespace CryptoTrade\Models;

use JsonSerializable;

class User implements JsonSerializable
{
    private float $balance;
    private array $wallet;

    public function __construct(float $balance = 1000.0)
    {
        $this->balance = $balance;
        $this->wallet = [];
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function addBalance(float $amount): void
    {
        $this->balance += $amount;
    }

    public function subtractBalance(float $amount): void
    {
        $this->balance -= $amount;
    }

    public function getWallet(): array
    {
        return $this->wallet;
    }

    public function setWallet(array $wallet): void
    {
        $this->wallet = $wallet;
    }

    public function jsonSerialize(): array
    {
        return [
            'balance' => $this->balance,
            'wallet' => $this->wallet
        ];
    }
}
