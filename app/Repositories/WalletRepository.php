<?php

namespace CryptoTrade\Repositories;

use Doctrine\DBAL\Connection;
use CryptoTrade\Models\Wallet;
use Doctrine\DBAL\Exception;

class WalletRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    public function findWalletByUserId(int $userId): Wallet
    {
        $walletData = $this->connection->fetchAllAssociative('SELECT * FROM wallets WHERE user_id = ?', [$userId]);
        $wallet = new Wallet();
        foreach ($walletData as $data) {
            $wallet->addToWallet($data['symbol'], (float)$data['amount'], (float)$data['purchasePrice']);
        }
        return $wallet;
    }

    /**
     * @throws Exception
     */
    public function saveWallet(int $userId, Wallet $wallet): void
    {
        $this->connection->executeStatement('DELETE FROM wallets WHERE user_id = ?', [$userId]);
        foreach ($wallet->getWallet() as $symbol => $details) {
            $this->connection->insert('wallets', [
                'user_id' => $userId,
                'symbol' => $symbol,
                'amount' => $details['amount'],
                'purchasePrice' => $details['purchasePrice']
            ]);
        }
    }
}
