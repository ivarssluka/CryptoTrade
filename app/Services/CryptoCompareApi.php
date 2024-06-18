<?php

namespace CryptoTrade\Services;

use GuzzleHttp\Client;
use CryptoTrade\Models\CryptoCurrency;

class CryptoCompareApi implements ApiClientInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://min-api.cryptocompare.com/data/',
            'headers' => [
                'authorization' => 'Apikey ' . $_ENV['CRYPTOCOMPARE_API_KEY']
            ]
        ]);
    }

    public function getTopCryptos(int $limit = 10): array
    {
        $response = $this->client->get('top/mktcapfull', [
            'query' => ['limit' => $limit, 'tsym' => 'USD']
        ]);
        $cryptoData = json_decode($response->getBody()->getContents(), true);
        $cryptos = [];
        foreach ($cryptoData['Data'] as $item) {
            $cryptos[] = new CryptoCurrency(
                $item['CoinInfo']['Id'],
                $item['CoinInfo']['Name'],
                $item['CoinInfo']['FullName'],
                $item['RAW']['USD']['PRICE']
            );
        }
        return $cryptos;
    }

    public function getCryptoBySymbol(string $symbol): ?CryptoCurrency
    {
        $response = $this->client->get('price', [
            'query' => ['fsym' => $symbol, 'tsyms' => 'USD']
        ]);
        $coinData = json_decode($response->getBody()->getContents(), true);
        if (isset($coinData['USD'])) {
            return new CryptoCurrency(
                $symbol,
                $symbol,
                $symbol,
                $coinData['USD']
            );
        }
        return null;
    }
}