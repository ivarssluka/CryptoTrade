<?php

namespace CryptoTrade\Services;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use CryptoTrade\Models\CryptoCurrency;

class CoinMarketCapApi
{
    private Client $client;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/',
            'headers' => [
                'X-CMC_PRO_API_KEY' => $_ENV['CMC_API_KEY']
            ]
        ]);
    }

    public function getTopCryptos(int $limit = 10): array
    {
        $response = $this->client->get('cryptocurrency/listings/latest', [
            'query' => ['limit' => $limit]
        ]);
        $cryptoData = json_decode($response->getBody()->getContents());
        $cryptos = [];
        foreach ($cryptoData->data as $item) {
            $cryptos[] = new CryptoCurrency(
                $item->id,
                $item->name,
                $item->symbol,
                $item->quote->USD->price
            );
        }
        return $cryptos;
    }

    public function getCryptoBySymbol(string $symbol): ?CryptoCurrency
    {
        $response = $this->client->get('cryptocurrency/quotes/latest', [
            'query' => ['symbol' => $symbol]
        ]);
        $coinData = json_decode($response->getBody()->getContents());
        if (isset($coinData->data->$symbol)) {
            $item = $coinData->data->$symbol;
            return new CryptoCurrency(
                $item->id,
                $item->name,
                $item->symbol,
                $item->quote->USD->price
            );
        }
        return null;
    }
}
