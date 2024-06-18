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

    public function __construct(
        ApiClientInterface $apiClient,
        Database $database,
        User $user)
    {
        $this->conn = $database->getConnection();
        $this->apiClient = $apiClient;
        $this->user = $user;
        $this->loadWallet();
    }

    public function purchaseCrypto(
        CryptoCurrency $crypto,
        float $amount
    ): bool
    {
        $cost = $crypto->getPrice() * $amount;

        if ($this->user->getBalance() < $cost) {
            return false;
        }
        $this->user->subtractBalance($cost);
        $this->user->addToWallet($crypto->getSymbol(), $amount, $crypto->getPrice());
        $this->saveTransaction(new Transaction($this->user->getId(), 'buy', $crypto->getSymbol(), $amount, $crypto->getPrice(), (new DateTime())->format('Y-m-d H:i:s')));
        $this->saveWallet();
        return true;
    }

    public function sellCrypto(
        CryptoCurrency $crypto,
        float $amount
    ): bool
    {
        $wallet = $this->user->getWallet();
        $symbol = $crypto->getSymbol();
        if (isset($wallet[$symbol]) === false || $wallet[$symbol]['amount'] < $amount) {
            return false;
        }
        $earnings = $crypto->getPrice() * $amount;
        $this->user->addBalance($earnings);
        $this->user->removeFromWallet($crypto->getSymbol(), $amount);
        $this->saveTransaction(new Transaction($this->user->getId(), 'sell', $crypto->getSymbol(), $amount, $crypto->getPrice(), (new DateTime())->format('Y-m-d H:i:s')));
        $this->saveWallet();
        return true;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTransactionHistory(): array
    {
        $transactionsData = $this->conn->fetchAllAssociative('SELECT * FROM transactions WHERE user_id = ?', [$this->user->getId()]);
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
            'user_id' => $transaction->getUserId(),
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

        $this->conn->executeStatement('DELETE FROM wallets WHERE user_id = ?', [$this->user->getId()]);

        foreach ($wallet as $symbol => $details) {
            $this->conn->insert('wallets', [
                'user_id' => $this->user->getId(),
                'symbol' => $symbol,
                'amount' => $details['amount'],
                'purchasePrice' => $details['purchasePrice']
            ]);
        }

        $this->conn->update('users', ['balance' => $this->user->getBalance()], ['id' => $this->user->getId()]);
    }

    private function loadWallet(): void
    {
        $walletData = $this->conn->fetchAllAssociative('SELECT * FROM wallets WHERE user_id = ?', [$this->user->getId()]);
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
