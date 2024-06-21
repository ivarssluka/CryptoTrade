<?php

namespace CryptoTrade\Services\User;

use CryptoTrade\Models\User;
use CryptoTrade\Repositories\UserRepository;
use Doctrine\DBAL\Exception;

class BalanceService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws Exception
     */
    public function addBalance(
        User $user,
        float $amount
    ): void
    {
        $user->addBalance($amount);
        $this->userRepository->update($user);
    }

    /**
     * @throws Exception
     */
    public function subtractBalance(
        User $user,
        float $amount
    ): void
    {
        $user->subtractBalance($amount);
        $this->userRepository->update($user);
    }
}
