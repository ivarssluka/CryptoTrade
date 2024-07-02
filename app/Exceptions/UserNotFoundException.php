<?php

namespace CryptoTrade\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'User not found. Please check your credentials and try again.';
}
