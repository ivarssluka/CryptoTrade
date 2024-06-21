<?php

namespace CryptoTrade\Models;

use JsonSerializable;

class Wallet implements JsonSerializable
{
    private array $wallet;

    public function __construct(array $wallet = [])
    {
        $this->wallet = $wallet;
    }

    public function getWallet(): array
    {
        return $this->wallet;
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

    public function removeFromWallet(
        string $symbol,
        float $amount
    ): void
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
        return $this->wallet;
    }
}
