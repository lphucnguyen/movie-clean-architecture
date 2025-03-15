<?php

namespace App\Infrastructure\Services\Payment;

use App\Application\DTOs\Payment\ApprovalPaymentDTO;
use App\Shared\Application\DTOs\BaseDTO;
use App\Application\DTOs\Payment\PaypalDTO;
use App\Shared\Infrastructure\Concerns\ConsumeExternalService;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class PaypalService implements IPaymentService
{
    use ConsumeExternalService;

    private $baseUri;
    private $clientId;
    private $clientSecret;
    private $plans;

    public function __construct()
    {
        $this->baseUri = config('services.paypal.base_uri');
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->plans = config('services.paypal.plans');
    }

    private function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['Authorization'] = $this->resolveAccessToken();
    }

    private function decodeResponse($response)
    {
        return json_decode($response);
    }

    private function resolveAccessToken() {
        return "Basic " . base64_encode("{$this->clientId}:{$this->clientSecret}");
    }

    public function handlePayment(BaseDTO $paypalDTO)
    {
        if (!$paypalDTO instanceof PaypalDTO) {
            throw new InvalidArgumentException('Expected StripeDTO');
        }

        $encryptedData = Crypt::encryptString(json_encode([
            'plan_id' => $paypalDTO->plan_id,
            'order_id' => $paypalDTO->order_id,
            'payment_name' => $paypalDTO->payment_name,
        ]));


        $order = $this->createAuthorization(
            $paypalDTO->amount,
            config('services.currency'),
            route('approval',
                [
                    'encrypt_data' => $encryptedData
                ]
            ),
            route('cancelled')
        );

        $orderLinks = collect($order->links);

        $approve = $orderLinks->where('rel', 'approve')->first();

        return redirect($approve->href);
    }

    public function handleApproval(BaseDTO $approvalDTO)
    {
        if (!($approvalDTO instanceof ApprovalPaymentDTO)) {
            throw new InvalidArgumentException('Expected ApprovalPaymentDTO');
        }

        try {
            $payment = $this->authorization($approvalDTO->token);

            if ($payment->status === 'COMPLETED') {
                $captureId = $payment->purchase_units[0]->payments->authorizations[0]->id;

                return $captureId;
            } else {
                throw new \Exception();
            }
        } catch(\Exception $e) {
            throw new \Exception('Handle Payment Fail');
        }
    }

    public function capturePayment($approvalId)
    {
        return $this->makeRequest(
            'POST',
            "/v2/checkout/orders/{$approvalId}/capture",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function captureAuthorization($authorizeId)
    {
        return $this->makeRequest(
            'POST',
            "/v2/payments/authorizations/{$authorizeId}/capture",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function authorization($authorizeId)
    {
        return $this->makeRequest(
            'POST',
            "/v2/checkout/orders/{$authorizeId}/authorize",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function declineAuthorization($authorizeId)
    {
        return $this->makeRequest(
            'POST',
            "/v2/payments/authorizations/{$authorizeId}/void",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function createOrder($value, $currency, $returnUrl, $cancelUrl)
    {
        return $this->makeRequest(
            'POST',
            '/v2/checkout/orders',
            [],
            [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' =>strtoupper($currency),
                            'value' => round($value * $factor = $this->resolveFactor($currency)) / $factor,
                        ]
                    ]
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ]
            ],
            [
                'PayPal-Request-Id' => str()->uuid()->toString()
            ],
            $isJsonRequest = true,
        );
    }

    public function createAuthorization($value, $currency, $returnUrl, $cancelUrl)
    {
        return $this->makeRequest(
            'POST',
            '/v2/checkout/orders',
            [],
            [
                'intent' => 'AUTHORIZE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' =>strtoupper($currency),
                            'value' => round($value * $factor = $this->resolveFactor($currency)) / $factor,
                        ]
                    ]
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ]
            ],
            [],
            $isJsonRequest = true,
        );
    }

    public function getPayment($paymentId)
    {
        return $this->makeRequest(
            'GET',
            "/v2/checkout/orders/{$paymentId}",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function resolveFactor($currency)
    {
        $zeroDecimalCurrencies = ['JPY'];

        if (in_array(strtoupper($currency), $zeroDecimalCurrencies)) {
            return 1;
        }

        return 100;
    }

    public function cancelPayment($approvalId)
    {
        return $this->makeRequest(
            'DELETE',
            "v1/checkout/orders/{$approvalId}",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function refundPayment($approvalId) {
        return $this->makeRequest(
            'POST',
            "/v2/payments/captures/{$approvalId}/refund",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }
}
