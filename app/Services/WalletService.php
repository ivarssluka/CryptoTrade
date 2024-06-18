<?php

namespace CryptoTrade\Services;

use CryptoTrade\Models\User;
use CryptoTrade\Models\CryptoCurrency;
use CryptoTrade\Models\Transaction;
use Doctrine\DBAL\Connection;
use DateTime;

class WalletService
{
    private User $user;
    private Connection $conn;
    private ApiClientInterface $apiClient;

    public function __construct(ApiClientInterface $apiClient, Database $database)
    {
        $this->conn = $database->getConnection();
        $this->apiClient = $apiClient;
        $this->loadUser();
    }

    private function loadUser(): void
    {
        $userData = $this->conn->fetchAssociative('SELECT * FROM user_balance WHERE id = 1');
        if ($userData) {
            $this->user = new User();
            $this->user->setBalance((float)$userData['balance']);
            $this->loadWallet();
        } else {
            $this->user = new User();
        }
    }

    public function purchaseCrypto(CryptoCurrency $crypto, float $amount): bool
    {
        $cost = $crypto->getPrice() * $amount;

        if ($this->user->getBalance() < $cost) {
            return false;
        }
        $this->user->subtractBalance($cost);
        $this->user->addToWallet($crypto->getSymbol(), $amount, $crypto->getPrice());
        $this->saveTransaction(new Transaction('buy', $crypto->getSymbol(), $amount, $crypto->getPrice(), (new DateTime())->format('Y-m-d H:i:s')));
        $this->saveWallet();
        return true;
    }

    public function sellCrypto(CryptoCurrency $crypto, float $amount): bool
    {
        $wallet = $this->user->getWallet();
        $symbol = $crypto->getSymbol();
        if (!isset($wallet[$symbol]) || $wallet[$symbol]['amount'] < $amount) {
            return false;
        }
        $earnings = $crypto->getPrice() * $amount;
        $this->user->addBalance($earnings);
        $this->user->removeFromWallet($crypto->getSymbol(), $amount);

        if ($wallet[$symbol]['amount'] <= 0) {
            unset($wallet[$symbol]);
        }

        $this->saveTransaction(new Transaction('sell', $crypto->getSymbol(), $amount, $crypto->getPrice(), (new DateTime())->format('Y-m-d H:i:s')));
        $this->saveWallet();
        return true;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTransactionHistory(): array
    {
        $transactionsData = $this->conn->fetchAllAssociative('SELECT * FROM transactions');
        return array_map(function ($transactionData) {
            return Transaction::fromObject((object)$transactionData);
        }, $transactionsData);
    }

    public function getWalletOverview(): array
    {
        $overview = [];
        $wallet = $this->user->getWallet();

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

    private function saveTransaction(Transaction $transaction): void
    {
        $this->conn->insert('transactions', [
            'type' => $transaction->getType(),
            'symbol' => $transaction->getSymbol(),
            'amount' => $transaction->getAmount(),
            'price' => $transaction->getPrice(),
            'timestamp' => $transaction->getTimestamp()
        ]);
    }

    public function saveWallet(): void
    {
        $wallet = $this->user->getWallet();

        $this->conn->executeStatement('DELETE FROM wallet');

        foreach ($wallet as $symbol => $details) {
            $this->conn->insert('wallet', [
                'symbol' => $symbol,
                'amount' => $details['amount'],
                'purchasePrice' => $details['purchasePrice']
            ]);
        }

        $this->conn->update('user_balance', ['balance' => $this->user->getBalance()], ['id' => 1]);
    }

    private function loadWallet(): void
    {
        $walletData = $this->conn->fetchAllAssociative('SELECT * FROM wallet');
        $wallet = [];
        foreach ($walletData as $data) {
            $wallet[$data['symbol']] = [
                'amount' => (float)$data['amount'],
                'purchasePrice' => (float)$data['purchasePrice']
            ];
        }
        $this->user->setWallet($wallet);
    }
}
