<?php

namespace App\Application\Commands\Payment;

use App\Application\DTOs\Payment\ApprovalPaymentDTO;

class ApprovalCommand
{
    public function __construct(
        public ApprovalPaymentDTO $dto
    ) {
    }
}
