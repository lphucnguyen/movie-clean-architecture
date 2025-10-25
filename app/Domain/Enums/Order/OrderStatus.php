<?php

namespace App\Domain\Enums\Order;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case FAIL = 'fail';
}