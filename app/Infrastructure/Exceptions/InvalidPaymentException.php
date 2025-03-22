<?php

namespace App\Infrastructure\Exceptions;

class InvalidPaymentException extends \Exception
{
    protected $message = 'Payment hiện tại không hợp lệ';
}