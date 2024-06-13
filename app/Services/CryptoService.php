<?php

namespace CryptoTrade\Services;

use GuzzleHttp\Client;
use CryptoTrade\Models\Crypto;
use CryptoTrade\Contracts\ApiClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Dotenv\Dotenv;

class CryptoService implements ApiClientInterface
{
    private Client $client;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable('.');
        $dotenv->load();

        $cryptoCompareApiKey = $_ENV['CRYPTO_COMPARE_API_KEY'];

        $this->client = new Client([
            'base_uri' => 'https://min-api.cryptocompare.com/data/',
            'headers' => [
                'authorization' => 'Apikey ' . $cryptoCompareApiKey
            ]
        ]);
    }

    public function getTopCryptos(int $limit = 10): array
    {
        try {
            $response = $this->client->get('top/mktcapfull', [
                'query' => ['limit' => $limit, 'tsym' => 'USD']
            ]);
        } catch (GuzzleException $e) {
            return [];
        }
        $cryptoData = json_decode($response->getBody()->getContents());
        $cryptos = [];
        foreach ($cryptoData->Data as $item) {
            $cryptos[] = new Crypto(
                $item->CoinInfo->Id,
                $item->CoinInfo->Name,
                $item->CoinInfo->FullName,
                $item->RAW->USD->PRICE
            );
        }
        return $cryptos;
    }

    public function getCryptoBySymbol(string $symbol): ?Crypto
    {
        try {
            $response = $this->client->get('price', [
                'query' => ['fsym' => $symbol, 'tsyms' => 'USD']
            ]);
        } catch (GuzzleException $e) {
            return null;
        }
        $coinData = json_decode($response->getBody()->getContents());
        if (isset($coinData->USD)) {
            return new Crypto(
                $symbol,
                $symbol,
                $symbol,
                $coinData->USD,
            );
        }
        return null;
    }
}
