<?php

namespace App\Application\Jobs;

use App\Domain\Enums\Order\OrderStatus;
use App\Domain\Repositories\IOrderRepository;
use App\Infrastructure\Services\Payment\PaymentResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlePaymentJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public int $delay = 10;

    public function __construct(
        private int $size
    ) {
    }

    public function handle(
        PaymentResolver $paymentResolver,
        IOrderRepository $orderRepository,
    ) {
        $orders = collect($orderRepository->getUnpaidOrder($this->size));

        foreach ($orders as $order) {
            try {
                DB::beginTransaction();

                $paymentService = $paymentResolver->resolveService($order->payment_name);
                $paymentService->captureAuthorization($order->transaction_id);

                $orderRepository->update($order->id, [
                    'paid_at' => now()
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                // Notification to Slack
                $orderRepository->update($order->id, [
                    'status' => OrderStatus::FAIL->value,
                ]);
                Log::error("Settlement failed for Order ID: {$order->id}. Error: " . $e->getMessage());
            }
        }

        if ($orders->count() === $this->size) {
            $this->release($this->delay);
        }
    }
}
