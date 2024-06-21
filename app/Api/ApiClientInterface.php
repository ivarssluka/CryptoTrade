<?php

namespace CryptoTrade\Api;

use CryptoTrade\Models\CryptoCurrency;

interface ApiClientInterface
{
    public function getTopCryptos(int $limit = 10): array;
    public function getCryptoBySymbol(string $symbol): ?CryptoCurrency;
}
