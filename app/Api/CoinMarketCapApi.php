<?php

namespace CryptoTrade\Api;

use Exception;
use GuzzleHttp\Client;
use CryptoTrade\Models\CryptoCurrency;
use Psr\Http\Message\ResponseInterface;

class CoinMarketCapApi implements ApiClientInterface
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://pro-api.coinmarketcap.com/v1/',
            'headers' => [
                'X-CMC_PRO_API_KEY' => $_ENV['CMC_API_KEY']
            ]
        ]);
    }

    /**
     * @throws Exception
     */
    private function getResponseData(ResponseInterface $response): array
    {
        $data = json_decode($response->getBody()->getContents(), true);
        return $data['data'];
    }

    public function getTopCryptos(int $limit = 10): array
    {
        try {
            $response = $this->client->get('cryptocurrency/listings/latest', [
                'query' => ['limit' => $limit]
            ]);
            $cryptoData = $this->getResponseData($response);
            $cryptos = [];
            foreach ($cryptoData as $item) {
                $cryptos[] = new CryptoCurrency(
                    $item['id'],
                    $item['name'],
                    $item['symbol'],
                    $item['quote']['USD']['price']
                );
            }
            return $cryptos;
        } catch (Exception $e) {
            echo "Error fetching top cryptocurrencies: " . $e->getMessage() . "\n";
            return [];
        }
    }

    public function getCryptoBySymbol(string $symbol): ?CryptoCurrency
    {
        try {
            $response = $this->client->get('cryptocurrency/quotes/latest', [
                'query' => ['symbol' => $symbol]
            ]);
            $coinData = $this->getResponseData($response);
            if (isset($coinData[$symbol])) {
                $item = $coinData[$symbol];
                return new CryptoCurrency(
                    $item['id'],
                    $item['name'],
                    $item['symbol'],
                    $item['quote']['USD']['price']
                );
            }
            return null;
        } catch (Exception $e) {
            echo "Error fetching cryptocurrency by symbol: " . $e->getMessage() . "\n";
            return null;
        }
    }
}
