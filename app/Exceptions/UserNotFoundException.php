<?php

namespace CryptoTrade\Exceptions;

class UserNotFoundException extends AppException
{
    public function __construct(string $message = "User not found")
    {
        parent::__construct($message);
    }
}
