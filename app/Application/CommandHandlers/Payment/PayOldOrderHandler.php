<?php

namespace App\Application\CommandHandlers\Payment;

use App\Application\Commands\Payment\PayOldOrderCommand;
use App\Application\Exceptions\ExistAnotherOrderException;
use App\Application\Exceptions\InvalidOrderException;
use App\Domain\Enums\Order\OrderStatus;
use App\Domain\Repositories\IOrderRepository;
use App\Domain\Repositories\IPlanRepository;
use App\Infrastructure\Services\Payment\PaymentResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayOldOrderHandler
{
    private int $LOCK_TIME;

    public function __construct(
        private PaymentResolver $paymentResolver,
        private IPlanRepository $planRepository,
        private IOrderRepository $orderRepository
    ) {
        $this->LOCK_TIME = config('app.payment.lock_time');
    }

    public function handle(PayOldOrderCommand $command)
    {
        try {
            DB::beginTransaction();

            $lock = cache()->lock(auth()->user()->id . ':payment:send', $this->LOCK_TIME);
            if (!$lock->get()) {
                return throw new \Exception(__('Hiện tại đăng có một yêu cầu thanh toán. Hãy cố gắng lại lần nữa sau ít phút.'));
            }

            $dto = $command->dto;
            $plan = $this->planRepository->get($dto->plan_id);
            $dto->amount = $plan->price;

            $this->handleExistOrder($dto->order_id);

            $paymentService = $this->paymentResolver->resolveService($dto->payment_name);
            return $paymentService->handlePayment($dto);

            DB::commit();
        } catch(ExistAnotherOrderException $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __('Bạn đã có một đơn hàng đang được xử lý. Vui lòng chờ đợi.'));
        } catch(InvalidOrderException $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __('Đơn hàng đã được xử lý hoặc đã bị hủy.'));
        } catch(\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __("Không thể xứ lý đơn hàng"));
        } finally {
            $lock->release();
        }
    }

    public function isCanPay(string $orderId) {
        $query =  $this->orderRepository->makeQuery()
            ->where('user_id', auth()->user()->id)
            ->where('id', '<>', $orderId)
            ->where('status', OrderStatus::PROCESSING)
            ->count();

        return $query < 1;
    }

    public function handleExistOrder(string $orderId) {
        $order = $this->orderRepository->getWithLock($orderId);

        if (!$this->isCanPay($order->id)) {
            throw new ExistAnotherOrderException();
        }

        if ($order->status === OrderStatus::COMPLETED->value || $order->status === OrderStatus::CANCELED->value) {
            throw new InvalidOrderException();
        }

        return $order;
    }
}
