<?php

namespace CryptoTrade\Exceptions;

use Exception;

class CryptoNotFoundException extends Exception
{
    protected $message = 'Cryptocurrency not found.';
}
