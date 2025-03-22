<?php

namespace App\Application\Exceptions;

class ExistAnotherOrderException extends \Exception
{
    protected $message = 'Bạn đã có một đơn hàng đang được xử lý. Vui lòng chờ đợi.';
}
