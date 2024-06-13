<?php

namespace CryptoTrade\Services;

use CryptoTrade\Models\User;
use CryptoTrade\Models\Crypto;
use CryptoTrade\Models\Transaction;
use DateTime;
use CryptoTrade\Contracts\ApiClientInterface;

class WalletService
{
    private User $user;
    private string $transactionsFile;
    private string $walletFile;
    private ApiClientInterface $cryptoService;

    public function __construct(ApiClientInterface $cryptoService)
    {
        $this->user = new User();
        $this->transactionsFile = __DIR__ . '/../Storage/transactions.json';
        $this->walletFile = __DIR__ . '/../Storage/wallet.json';
        $this->cryptoService = $cryptoService;
        $this->loadWallet();
    }

    public function purchaseCrypto(Crypto $crypto, float $amount): bool
    {
        $cost = $crypto->getPrice() * $amount;

        if ($this->user->getBalance() < $cost) {
            return false;
        }
        $this->user->subtractBalance($cost);
        $this->user->addToWallet($crypto->getSymbol(), $amount, $crypto->getPrice());
        $this->saveTransaction(
            new Transaction(
                'buy',
                $crypto->getSymbol(),
                $amount,
                $crypto->getPrice(),
                (new DateTime())->format('Y-m-d H:i:s'))
        );
        $this->saveWallet();
        return true;
    }

    public function sellCrypto(Crypto $crypto, float $amount): bool
    {
        $wallet = $this->user->getWallet();
        $symbol = $crypto->getSymbol();
        if (isset($wallet[$symbol]) === false || $wallet[$symbol]['amount'] < $amount) {
            return false;
        }
        $earnings = $crypto->getPrice() * $amount;
        $this->user->addBalance($earnings);
        $this->user->removeFromWallet($crypto->getSymbol(), $amount);
        $this->saveTransaction(
            new Transaction(
                'sell',
                $crypto->getSymbol(),
                $amount,
                $crypto->getPrice(),
                (new DateTime())->format('Y-m-d H:i:s'))
        );
        $this->saveWallet();
        return true;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTransactionHistory(): array
    {
        $transactionsData = json_decode(file_get_contents($this->transactionsFile), true) ?? [];
        if ($transactionsData === null) {
            return [];
        }
        return array_map(function ($transactionData) {
            return Transaction::fromObject((object)$transactionData);
        }, $transactionsData);
    }

    public function getWalletOverview(): array
    {
        $overview = [];
        $wallet = $this->user->getWallet();

        $cryptoService = new CryptoService();

        foreach ($wallet as $symbol => $details) {
            $currentCrypto = $cryptoService->getCryptoBySymbol($symbol);
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

    private function saveTransaction(Transaction $transaction): void
    {
        $transactions = $this->getTransactionHistory();
        $transactions[] = $transaction;
        file_put_contents($this->transactionsFile, json_encode($transactions, JSON_PRETTY_PRINT));
    }

    public function saveWallet(): void
    {
        file_put_contents($this->walletFile, json_encode($this->user, JSON_PRETTY_PRINT));
    }

    private function loadWallet(): void
    {
        if (file_exists($this->walletFile)) {
            $data = json_decode(file_get_contents($this->walletFile), true);
            if ($data !== null) {
                $this->user->setBalance((float)($data['balance'] ?? 1000.0));
                $this->user->setWallet((array)($data['wallet'] ?? []));
            }
        }
    }
}
