<?php

namespace CryptoTrade\Services\Transactions;

use CryptoTrade\Repositories\TransactionRepository;
use CryptoTrade\Models\Transaction;
use Doctrine\DBAL\Exception;

class TransactionService
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @throws Exception
     */
    public function saveTransaction(Transaction $transaction): void
    {
        $this->transactionRepository->save($transaction);
    }

    /**
     * @throws Exception
     */
    public function getTransactionHistory(int $userId): array
    {
        return $this->transactionRepository->findByUserId($userId);
    }
}
