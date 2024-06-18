<?php

namespace CryptoTrade\Models;

use JsonSerializable;

class Transaction implements JsonSerializable
{
    private int $userId;
    private string $type;
    private string $symbol;
    private float $amount;
    private float $price;
    private string $timestamp;

    public function __construct(
        int $userId,
        string $type,
        string $symbol,
        float $amount,
        float $price,
        string $timestamp
    )
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->symbol = $symbol;
        $this->amount = $amount;
        $this->price = $price;
        $this->timestamp = $timestamp;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->userId,
            'type' => $this->type,
            'symbol' => $this->symbol,
            'amount' => $this->amount,
            'price' => $this->price,
            'timestamp' => $this->timestamp,
        ];
    }

    public static function fromObject($data): self
    {
        return new self(
            $data->user_id,
            $data->type,
            $data->symbol,
            (float)$data->amount,
            (float)$data->price,
            $data->timestamp
        );
    }
}
