<?php

namespace App\Infrastructure\Services\Payment;

use App\Application\DTOs\Payment\ApprovalPaymentDTO;
use App\Shared\Application\DTOs\BaseDTO;
use App\Application\DTOs\Payment\StripeDTO;
use App\Shared\Infrastructure\Concerns\ConsumeExternalService;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class StripeService implements IPaymentService
{
    use ConsumeExternalService;

    private string $baseUri;
    private string $clientId;
    private string $clientSecret;
    private array $plans;

    public function __construct()
    {
        $this->baseUri = config('services.stripe.base_uri');
        $this->clientId = config('services.stripe.key');
        $this->clientSecret = config('services.stripe.secret');
        $this->plans = config('services.stripe.plans');
    }

    public function handlePayment(BaseDTO $stripeDTO)
    {
        if (!($stripeDTO instanceof StripeDTO)) {
            throw new InvalidArgumentException('Expected StripeDTO');
        }

        $encryptedData = Crypt::encryptString(json_encode([
            'plan_id' => $stripeDTO->plan_id,
            'order_id' => $stripeDTO->order_id,
            'payment_name' => $stripeDTO->payment_name,
        ]));

        $session = $this->createSesssion(
            $stripeDTO->amount,
            config('services.currency'),
            route('approval', [
                'encrypt_data' => $encryptedData,
            ]) . '&token={CHECKOUT_SESSION_ID}',
            route('cancelled'),
        );

        return redirect($session->url);
    }

    public function handleApproval(BaseDTO $approvalDTO)
    {
        if (!($approvalDTO instanceof ApprovalPaymentDTO)) {
            throw new InvalidArgumentException('Expected ApprovalPaymentDTO');
        }

        try {
            $session = $this->getSession($approvalDTO->token);

            if ($session->status == 'complete') {
                return $session->payment_intent;
            } else {
                throw new \Exception();
            }
        } catch(\Exception $e) {
            throw new \Exception('Handle Payment Fail');
        }
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['Authorization'] = $this->resolveAccessToken();
    }

    private function resolveAccessToken()
    {
        return "Bearer {$this->clientSecret}";
    }

    public function decodeResponse($response)
    {
        return json_decode($response);
    }

    public function createIntent($value, $currency, $paymentMethod)
    {
        return $this->makeRequest(
            'POST',
            '/v1/payment_intents',
            [],
            [
                'amount' => round($value * $this->resolveFactor($currency)),
                'currency' => strtolower($currency),
                'payment_method' => $paymentMethod,
                'confirmation_method' => 'manual',
            ]
        );
    }

    public function createSesssion($value, $currency, $returnUrl, $cancelUrl)
    {
        $checkoutSession = $this->makeRequest(
            'POST',
            '/v1/checkout/sessions',
            [],
            [
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => strtolower($currency),
                            'product_data' => [
                                'name' => 'Upgrade User',
                            ],
                            'unit_amount' => $value * 100,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'payment_method_types' => ['card'],
                'payment_intent_data' => [
                    'capture_method' => 'manual',
                ],
                'mode' => 'payment',
                'success_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
            [
                'Idempotency-Key' => str()->uuid()->toString()
            ]
        );

        return $checkoutSession;
    }

    public function createAuthorization($value, $currency, $paymentMethod)
    {
        return $this->makeRequest(
            'POST',
            '/v1/payment_intents',
            [],
            [
                'amount' => round($value * $this->resolveFactor($currency)),
                'currency' => strtolower($currency),
                'payment_method' => $paymentMethod,
                'capture_method' => 'manual',
            ]
        );
    }

    public function getPayment($paymentIntentId)
    {
        return $this->makeRequest(
            'GET',
            "/v1/payment_intents/{$paymentIntentId}",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function getSession($sessionToken)
    {
        return $this->makeRequest(
            'GET',
            "/v1/checkout/sessions/{$sessionToken}",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    private function resolveFactor($currency)
    {
        $zeroDecimalCurrencies = ['JPY'];

        if (in_array(strtoupper($currency), $zeroDecimalCurrencies)) {
            return 1;
        }

        return 100;
    }

    public function confirmPayment($paymentIntentId)
    {
        return $this->makeRequest(
            'POST',
            "/v1/payment_intents/{$paymentIntentId}/confirm",
            [],
            [
                'return_url' => route('approval'),
            ]
        );
    }

    public function captureAuthorization($authorizeId)
    {
        return $this->makeRequest(
            'POST',
            "/v1/payment_intents/{$authorizeId}/capture",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ],
        );
    }

    public function declineAuthorization($authorizeId)
    {
        return $this->cancelPayment($authorizeId);
    }

    public function cancelPayment($paymentIntentId)
    {
        return $this->makeRequest(
            'POST',
            "/v1/payment_intents/{$paymentIntentId}/cancel",
            [],
            [
                'cancellation_reason' => 'abandoned', // duplicate, fraudulent, requested_by_customer, or abandoned
            ]
        );
    }

    public function refundPayment($paymentIntentId)
    {
        return $this->makeRequest(
            'POST',
            "/v1/refunds",
            [],
            [
                'payment_intent' => $paymentIntentId,
                'reason' => 'requested_by_customer',
            ]
        );
    }
}