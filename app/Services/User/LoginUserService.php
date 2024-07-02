<?php

namespace CryptoTrade\Services\User;

use CryptoTrade\Exceptions\UserNotFoundException;
use CryptoTrade\Repositories\UserRepository;

class LoginUserService
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loginUser(string $username, string $password)
    {
        $user = $this->userRepository->findUserByUsername($username);
        if (!$user || !password_verify($password, $user->getPassword())) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
