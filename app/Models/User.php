<?php

namespace CryptoTrade\Models;

use JsonSerializable;

class User implements JsonSerializable
{
    private int $id;
    private string $username;
    private string $password;
    private float $balance;
    private array $wallet;

    public function __construct(int $id, string $username, string $password, float $balance = 1000.0)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->balance = $balance;
        $this->wallet = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
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

    public function addToWallet(string $symbol, float $amount, float $purchasePrice): void
    {
        if (isset($this->wallet[$symbol]) === false) {
            $this->wallet[$symbol] = [
                'amount' => 0.0,
                'purchasePrice' => $purchasePrice
            ];
        }
        $this->wallet[$symbol]['amount'] += $amount;
        $this->wallet[$symbol]['purchasePrice'] = (($this->wallet[$symbol]['purchasePrice'] *
                    $this->wallet[$symbol]['amount']) + ($purchasePrice * $amount)) /
            ($this->wallet[$symbol]['amount'] + $amount);
    }

    public function removeFromWallet(string $symbol, float $amount): void
    {
        if (isset($this->wallet[$symbol])) {
            $this->wallet[$symbol]['amount'] -= $amount;
            if ($this->wallet[$symbol]['amount'] <= 0) {
                unset($this->wallet[$symbol]);
            }
        }
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
            'wallet' => $this->wallet
        ];
    }
}
