<?php

namespace CryptoTrade\Models;

use JsonSerializable;

class User implements JsonSerializable
{
    private float $balance;
    private array $wallet;

    public function __construct()
    {
        $this->balance = 1000.0;
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

    public function addToWallet(
        string $symbol,
        float $amount,
        float $purchasePrice
    ): void
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

    public function jsonSerialize(): array
    {
        return [
            'balance' => $this->balance,
            'wallet' => $this->wallet
        ];
    }
}