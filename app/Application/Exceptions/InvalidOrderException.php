<?php

namespace App\Application\Exceptions;

class InvalidOrderException extends \Exception
{
    protected $message = 'Đơn hàng đã được xử lý hoặc đã bị hủy.';
}
