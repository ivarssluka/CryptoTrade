<?php

namespace CryptoTrade\Services\User;

use CryptoTrade\Models\User;
use CryptoTrade\Repositories\UserRepository;
use CryptoTrade\Exceptions\UserNotFoundException;
use Doctrine\DBAL\Exception;

class LoginUserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function loginUser(
        string $username,
        string $password
    ): User
    {
        $user = $this->userRepository->findUserByUsername($username);

        if ($user && $user->verifyPassword($password)) {
            return $user;
        } else {
            throw new UserNotFoundException("Invalid username or password.");
        }
    }
}
