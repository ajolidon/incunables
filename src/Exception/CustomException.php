<?php

namespace App\Exception;

abstract class CustomException extends \Exception
{
    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code, null);
    }
}