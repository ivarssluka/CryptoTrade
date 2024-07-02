<?php

namespace CryptoTrade\Services\User;

use CryptoTrade\Exceptions\UserNotFoundException;
use CryptoTrade\Models\User;
use CryptoTrade\Repositories\UserRepository;
use Doctrine\DBAL\Exception;

class LoginUserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function loginUser(string $username, string $password): User
    {
        $user = $this->userRepository->findUserByUsername($username);
        if (!$user || !password_verify($password, $user->getPassword())) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
