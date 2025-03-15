<?php

namespace App\Application\DTOs\Payment;

use App\Shared\Application\DTOs\BaseDTO;

class ApprovalPaymentDTO extends BaseDTO {
    public string $plan_id;
    public string $order_id;
    public string $payment_name;
    public string $token;
}