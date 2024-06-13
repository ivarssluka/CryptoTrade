<?php

namespace CryptoTrade\Contracts;

use CryptoTrade\Models\Crypto;

interface ApiClientInterface
{
    public function getTopCryptos(int $limit = 10): array;

    public function getCryptoBySymbol(string $symbol): ?Crypto;
}