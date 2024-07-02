<?php

namespace CryptoTrade\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $message = 'Insufficient balance. Please check your account balance and try again.';
}
