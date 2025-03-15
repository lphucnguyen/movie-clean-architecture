<?php

namespace App\Application\CommandHandlers\Payment;

use App\Application\Commands\Payment\PayNewOrderCommand;
use App\Application\Events\OrderCreated;
use App\Application\Exceptions\ExistAnotherOrderException;
use App\Application\Exceptions\ProcessingAnotherOrderException;
use App\Domain\Enums\Order\OrderStatus;
use App\Domain\Repositories\IOrderRepository;
use App\Domain\Repositories\IPlanRepository;
use App\Infrastructure\Services\Payment\PaymentResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayNewOrderHandler
{
    private int $LOCK_TIME;

    public function __construct(
        private PaymentResolver $paymentResolver,
        private IPlanRepository $planRepository,
        private IOrderRepository $orderRepository
    ) {
        $this->LOCK_TIME = config('app.payment.lock_time');
    }

    public function handle(PayNewOrderCommand $command)
    {
        try {
            DB::beginTransaction();

            $lock = cache()->lock(auth()->user()->id . ':payment:send', $this->LOCK_TIME);
            if (!$lock->get()) {
                throw new ProcessingAnotherOrderException();
            }

            $dto = $command->dto;
            $plan = $this->planRepository->get($dto->plan_id);
            $dto->amount = $plan->price;

            $order = $this->handleNewOrder($dto->plan_id, $dto->payment_name, $dto->amount);
            $dto->order_id = $order->id;
            $paymentService = $this->paymentResolver->resolveService($dto->payment_name);
            event(new OrderCreated($order->id));

            return $paymentService->handlePayment($dto);

            DB::commit();
        } catch(ExistAnotherOrderException $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __('Bạn đã có một đơn hàng đang được xử lý. Vui lòng chờ đợi.'));
        } catch(ProcessingAnotherOrderException $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __('Hiện tại đang có một yêu cầu thanh toán. Hãy cố gắng lại lần nữa sau ít phút.'));
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

    public function isCanPay() {
        $query =  $this->orderRepository->makeQuery()
            ->where('user_id', auth()->user()->id)
            ->where('status', OrderStatus::PROCESSING)
            ->count();

        return $query < 1;
    }

    public function handleNewOrder($planId, $paymentName, $amount) {
        if (!$this->isCanPay()) {
            throw new ExistAnotherOrderException();
        }

        return $this->orderRepository->create([
            'user_id' => auth()->user()->id,
            'plan_id' => $planId,
            'payment_name' => $paymentName,
            'currency' => config('services.currency'),
            'amount' => $amount,
            'status' => OrderStatus::PROCESSING
        ]);
    }
}
