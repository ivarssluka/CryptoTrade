<?php

namespace CryptoTrade\Services\Wallet;

use CryptoTrade\Models\User;
use CryptoTrade\Models\CryptoCurrency;
use CryptoTrade\Models\Transaction;
use CryptoTrade\Repositories\TransactionRepository;
use DateTime;
use Doctrine\DBAL\Exception;

class PurchaseCryptoService
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function purchaseCrypto(
        User $user,
        CryptoCurrency $crypto,
        float $amount
    ): bool
    {
        $cost = $crypto->getPrice() * $amount;

        if ($user->getBalance() < $cost) {
            return false;
        }

        $user->subtractBalance($cost);
        $user->getWallet()->addToWallet($crypto->getSymbol(), $amount, $crypto->getPrice());
        $this->saveTransaction($user->getId(), $crypto, $amount);
        return true;
    }

    /**
     * @throws Exception
     */
    private function saveTransaction(
        int $userId,
        CryptoCurrency $crypto,
        float $amount
    ): void
    {
        $transaction = new Transaction(
            $userId,
            'buy',
            $crypto->getSymbol(),
            $amount, $crypto->getPrice(),
            (new DateTime())->format('Y-m-d H:i:s')
        );
        $this->transactionRepository->save($transaction);
    }
}
