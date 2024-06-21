<?php

namespace CryptoTrade\Services\Wallet;

use CryptoTrade\Models\CryptoCurrency;
use CryptoTrade\Models\User;
use CryptoTrade\Repositories\UserRepository;
use CryptoTrade\Services\Transactions\TransactionService;
use Doctrine\DBAL\Exception;

class WalletService
{
    private UserRepository $userRepository;
    private PurchaseCryptoService $purchaseCryptoService;
    private SellCryptoService $sellCryptoService;
    private WalletOverviewService $walletOverviewService;
    private TransactionService $transactionService;
    private User $user;

    public function __construct(
        UserRepository $userRepository,
        PurchaseCryptoService $purchaseCryptoService,
        SellCryptoService $sellCryptoService,
        WalletOverviewService $walletOverviewService,
        TransactionService $transactionService,
        User $user
    ) {
        $this->userRepository = $userRepository;
        $this->purchaseCryptoService = $purchaseCryptoService;
        $this->sellCryptoService = $sellCryptoService;
        $this->walletOverviewService = $walletOverviewService;
        $this->transactionService = $transactionService;
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function purchaseCrypto(
        CryptoCurrency $crypto,
        float $amount
    ): bool
    {
        if ($this->purchaseCryptoService->purchaseCrypto($this->user, $crypto, $amount)) {
            $this->saveWallet();
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function sellCrypto(
        CryptoCurrency $crypto,
        float $amount
    ): bool
    {
        if ($this->sellCryptoService->sellCrypto($this->user, $crypto, $amount)) {
            $this->saveWallet();
            return true;
        }
        return false;
    }

    public function getWalletOverview(): array
    {
        return $this->walletOverviewService->getWalletOverview($this->user);
    }

    public function getTransactionHistory(): array
    {
        return $this->transactionService->getTransactionHistory($this->user->getId());
    }

    public function saveWallet(): void
    {
        $this->userRepository->update($this->user);
    }
}
