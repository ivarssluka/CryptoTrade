<?php

namespace CryptoTrade\Services;

use CryptoTrade\Models\User;
use CryptoTrade\Models\Crypto;
use CryptoTrade\Models\Transaction;
use DateTime;
use CryptoTrade\Contracts\ApiClientInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class WalletService
{
    private User $user;
    private ApiClientInterface $cryptoService;
    private Connection $conn;

    /**
     * @throws Exception
     */
    public function __construct(ApiClientInterface $cryptoService, Connection $conn)
    {
        $this->cryptoService = $cryptoService;
        $this->conn = $conn;
        $this->user = new User();
        $this->loadWallet();
    }

    /**
     * @throws Exception
     */
    public function purchaseCrypto(Crypto $crypto, float $amount): bool
    {
        $cost = $crypto->getPrice() * $amount;
        if ($this->user->getBalance() < $cost) {
            return false;
        }
        $this->user->subtractBalance($cost);
        $this->addToWallet($crypto->getSymbol(), $amount, $crypto->getPrice());
        $this->saveTransaction(
            new Transaction(
                'buy',
                $crypto->getSymbol(),
                $amount,
                $crypto->getPrice(),
                (new DateTime())->format('Y-m-d H:i:s'))
        );
        $this->saveWallet();
        $this->saveUserBalance();
        return true;
    }

    /**
     * @throws Exception
     */
    public function sellCrypto(Crypto $crypto, float $amount): bool
    {
        $wallet = $this->user->getWallet();
        $symbol = $crypto->getSymbol();
        if (isset($wallet[$symbol]) === false || $wallet[$symbol]['amount'] < $amount) {
            return false;
        }
        $earnings = $crypto->getPrice() * $amount;
        $this->user->addBalance($earnings);
        $this->removeFromWallet($crypto->getSymbol(), $amount);
        $this->saveTransaction(
            new Transaction(
                'sell',
                $crypto->getSymbol(),
                $amount,
                $crypto->getPrice(),
                (new DateTime())->format('Y-m-d H:i:s'))
        );
        $this->saveWallet();
        $this->saveUserBalance();
        return true;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @throws Exception
     */
    public function getTransactionHistory(): array
    {
        $queryBuilder = $this->conn->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from('transactions');
        $transactionsData = $queryBuilder->executeQuery()->fetchAllAssociative();
        return array_map(function ($transactionData) {
            return Transaction::fromObject((object)$transactionData);
        }, $transactionsData);
    }

    public function getWalletOverview(): array
    {
        $overview = [];
        $wallet = $this->user->getWallet();
        foreach ($wallet as $symbol => $details) {
            $currentCrypto = $this->cryptoService->getCryptoBySymbol($symbol);
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

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
    public function saveWallet(): void
    {
        $this->conn->executeStatement('DELETE FROM wallet');
        $wallet = $this->user->getWallet();
        foreach ($wallet as $symbol => $details) {
            $this->conn->insert('wallet', [
                'symbol' => $symbol,
                'amount' => $details['amount'],
                'purchasePrice' => $details['purchasePrice']
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private function loadWallet(): void
    {
        $walletData = $this->conn->fetchAllAssociative('SELECT * FROM wallet');
        $wallet = [];
        foreach ($walletData as $item) {
            $wallet[$item['symbol']] = [
                'amount' => $item['amount'],
                'purchasePrice' => $item['purchasePrice']
            ];
        }
        $this->user->setWallet($wallet);
        $balance = $this->conn->fetchOne('SELECT balance FROM user_balance WHERE id = 1');
        if ($balance !== false) {
            $this->user->setBalance((float)$balance);
        } else {
            $this->user->setBalance(1000.0);
        }
    }

    /**
     * @throws Exception
     */
    public function saveUserBalance(): void
    {
        $balance = $this->user->getBalance();
        $this->conn->executeStatement('DELETE FROM user_balance WHERE id = 1');
        $this->conn->insert('user_balance', [
            'id' => 1,
            'balance' => $balance
        ]);
    }

    /**
     * @throws Exception
     */
    private function addToWallet(string $symbol, float $amount, float $purchasePrice): void
    {
        $wallet = $this->user->getWallet();
        if (isset($wallet[$symbol]) === false) {
            $wallet[$symbol] = ['amount' => 0.0, 'purchasePrice' => $purchasePrice];
        }
        $wallet[$symbol]['amount'] += $amount;
        $wallet[$symbol]['purchasePrice'] = $purchasePrice;
        $this->user->setWallet($wallet);
        $this->saveWallet();
    }

    /**
     * @throws Exception
     */
    private function removeFromWallet(string $symbol, float $amount): void
    {
        $wallet = $this->user->getWallet();
        if (isset($wallet[$symbol])) {
            $wallet[$symbol]['amount'] -= $amount;
            if ($wallet[$symbol]['amount'] <= 0) {
                unset($wallet[$symbol]);
            }
        }
        $this->user->setWallet($wallet);
        $this->saveWallet();
    }
}
