<?php

namespace CryptoTrade\Models;

use JsonSerializable;

class Crypto implements JsonSerializable
{
    private string $id;
    private string $name;
    private string $symbol;
    private float $price;

    public function __construct(string $id, string $name, string $symbol, float $price)
    {
        $this->id = $id;
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'price' => $this->price,
        ];
    }
}
