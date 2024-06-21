<?php

namespace CryptoTrade\Models;

use JsonSerializable;

class User implements JsonSerializable
{
    private int $id;
    private string $username;
    private string $password;
    private float $balance;
    private Wallet $wallet;

    public function __construct(
        int $id,
        string $username,
        string $password,
        float $balance = 1000.0,
        Wallet $wallet = null
    )
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->balance = $balance;
        $this->wallet = $wallet ?? new Wallet();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function addBalance(float $amount): void
    {
        $this->balance += $amount;
    }

    public function subtractBalance(float $amount): void
    {
        $this->balance -= $amount;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'balance' => $this->balance,
            'wallet' => $this->wallet->jsonSerialize()
        ];
    }
}
