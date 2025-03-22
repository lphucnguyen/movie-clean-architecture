<?php

namespace App\Application\Exceptions;

class ProcessingAnotherOrderException extends \Exception
{
    protected $message = 'Hiện tại đang có một yêu cầu thanh toán. Hãy cố gắng lại lần nữa sau ít phút.';
}
