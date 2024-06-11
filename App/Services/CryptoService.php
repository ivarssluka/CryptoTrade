<?php

namespace CryptoTrade\Services;

use GuzzleHttp\Client;
use CryptoTrade\Models\Crypto;

class CryptoService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/',
            'headers' => [
                'X-CMC_PRO_API_KEY' => 'd64d03ac-0003-46fd-89b0-972fa699cff3'
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
            $cryptos[] = new Crypto(
                $item->id,
                $item->name,
                $item->symbol,
                $item->quote->USD->price
            );
        }
        return $cryptos;
    }

    public function getCryptoBySymbol(string $symbol): ?Crypto
    {
        $response = $this->client->get('cryptocurrency/quotes/latest', [
            'query' => ['symbol' => $symbol]
        ]);
        $coinData = json_decode($response->getBody()->getContents());
        if (isset($coinData->data->$symbol)) {
            $item = $coinData->data->$symbol;
            return new Crypto(
                $item->id,
                $item->name,
                $item->symbol,
                $item->quote->USD->price
            );
        }
        return null;
    }
}
