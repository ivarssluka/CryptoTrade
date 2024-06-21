<?php

namespace CryptoTrade\Services\User;

use CryptoTrade\Models\User;
use CryptoTrade\Repositories\UserRepository;
use Doctrine\DBAL\Exception;

class RegisterUserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws Exception
     */
    public function registerUser(
        string $username,
        string $password
    ): void
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $user = new User(0, $username, $hashedPassword);

        $this->userRepository->save($user);
    }
}
