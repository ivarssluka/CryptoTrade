<?php

namespace CryptoTrade\Services\Wallet;

use CryptoTrade\Models\User;
use CryptoTrade\Api\ApiClientInterface;

class WalletOverviewService
{
    private ApiClientInterface $apiClient;

    public function __construct(ApiClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getWalletOverview(User $user): array
    {
        $overview = [];
        $wallet = $user->getWallet()->getWallet();

        foreach ($wallet as $symbol => $details) {
            $currentCrypto = $this->apiClient->getCryptoBySymbol($symbol);
            if ($currentCrypto) {
                $currentPrice = $currentCrypto->getPrice();
                $initialValue = $details['amount'] * $details['purchasePrice'];
                $currentValue = $details['amount'] * $currentPrice;
                $profitLoss = $currentValue - $initialValue;
                $overview[] = [
                    'symbol' => $symbol,
                    'amount' => $details['amount'],
                    'purchasePrice' => $details['purchasePrice'],
                    'currentPrice' => $currentPrice,
                    'initialValue' => $initialValue,
                    'currentValue' => $currentValue,
                    'profitLoss' => $profitLoss
                ];
            }
        }

        return $overview;
    }
}
