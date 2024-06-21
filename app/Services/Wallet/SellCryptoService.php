<?php

namespace CryptoTrade\Services\Wallet;

use CryptoTrade\Models\User;
use CryptoTrade\Models\CryptoCurrency;
use CryptoTrade\Models\Transaction;
use CryptoTrade\Repositories\TransactionRepository;
use DateTime;
use Doctrine\DBAL\Exception;

class SellCryptoService
{
    private TransactionRepository $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @throws Exception
     */
    public function sellCrypto(
        User $user,
        CryptoCurrency $crypto,
        float $amount
    ): bool
    {
        $wallet = $user->getWallet()->getWallet();
        $symbol = $crypto->getSymbol();

        if (isset($wallet[$symbol]) === false || $wallet[$symbol]['amount'] < $amount) {
            return false;
        }

        $earnings = $crypto->getPrice() * $amount;
        $user->addBalance($earnings);
        $user->getWallet()->removeFromWallet($crypto->getSymbol(), $amount);
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
            'sell',
            $crypto->getSymbol(),
            $amount, $crypto->getPrice(),
            (new DateTime())->format('Y-m-d H:i:s')
        );
        $this->transactionRepository->save($transaction);
    }
}
