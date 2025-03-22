<?php

namespace App\Infrastructure\Services\Payment;

use App\Infrastructure\Exceptions\InvalidPaymentException;

class PaymentResolver
{
    public function resolveService($serviceName)
    {
        $service = config("services.{$serviceName}.class");

        if ($service) {
            return resolve($service);
        }

        throw new InvalidPaymentException();
    }
}