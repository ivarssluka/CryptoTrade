<?php

namespace CryptoTrade\Repositories;

use Doctrine\DBAL\Connection;
use CryptoTrade\Models\Transaction;
use Doctrine\DBAL\Exception;

class TransactionRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws Exception
     */
    public function save(Transaction $transaction): void
    {
        $this->connection->insert('transactions', [
            'user_id' => $transaction->getUserId(),
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
    public function findByUserId(int $userId): array
    {
        $transactionsData = $this->connection->fetchAllAssociative('SELECT * FROM transactions WHERE user_id = ?', [$userId]);
        return array_map(function ($transactionData) {
            return Transaction::fromObject((object)$transactionData);
        }, $transactionsData);
    }
}
