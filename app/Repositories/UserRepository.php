<?php

namespace CryptoTrade\Repositories;

use Doctrine\DBAL\Connection;
use CryptoTrade\Models\User;
use CryptoTrade\Exceptions\UserNotFoundException;
use Doctrine\DBAL\Exception;

class UserRepository
{
    private Connection $connection;
    private WalletRepository $walletRepository;

    public function __construct(Connection $connection, WalletRepository $walletRepository)
    {
        $this->connection = $connection;
        $this->walletRepository = $walletRepository;
    }

    /**
     * @throws Exception
     */
    public function findUserByUsername(string $username): ?User
    {
        $userData = $this->connection->fetchAssociative('SELECT * FROM users WHERE username = ?', [$username]);
        if ($userData) {
            $wallet = $this->walletRepository->findWalletByUserId($userData['id']);
            return new User((int)$userData['id'], $userData['username'], $userData['password'], (float)$userData['balance'], $wallet);
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function save(User $user): void
    {
        $this->connection->insert('users', [
            'username' => $user->getUsername(),
            'password' => $user->getPassword(),
            'balance' => $user->getBalance(),
        ]);
        $userId = (int)$this->connection->lastInsertId();
        $this->walletRepository->saveWallet($userId, $user->getWallet());
    }

    /**
     * @throws Exception
     */
    public function update(User $user): void
    {
        $this->connection->update('users', [
            'balance' => $user->getBalance(),
        ], ['id' => $user->getId()]);

        $this->walletRepository->saveWallet($user->getId(), $user->getWallet());
    }
}
