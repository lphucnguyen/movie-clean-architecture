<?php

namespace App\Application\CommandHandlers\User;

use App\Application\Commands\User\CancelOrderCommand;
use App\Application\Exceptions\InvalidOrderException;
use App\Application\Exceptions\ProcessingAnotherOrderException;
use App\Domain\Enums\Order\OrderStatus;
use App\Domain\Repositories\IOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelOrderHandler
{
    private int $LOCK_TIME;

    public function __construct(
        private IOrderRepository $repository
    ) {
        $this->LOCK_TIME = config('app.payment.lock_time');
    }

    public function handle(CancelOrderCommand $command)
    {
        try {
            DB::beginTransaction();

            $order = $this->repository->getWithLock($command->uuid);
            if ($order->status === OrderStatus::CANCELED->value || $order->status === OrderStatus::COMPLETED->value) {
                throw new InvalidOrderException();
            }

            $lock = cache()->lock(auth()->user()->id . ':payment:send', $this->LOCK_TIME);
            if (!$lock->get()) {
                throw new ProcessingAnotherOrderException();
            }

            $this->repository->update($command->uuid, [
                'status' => OrderStatus::CANCELED->value
            ]);

            DB::commit();

            return redirect()->back()->withSuccess(__('Đơn hàng huỷ thành công'));
        } catch(ProcessingAnotherOrderException $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __($e->getMessage()));
        } catch(InvalidOrderException $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __($e->getMessage()));
        } catch(\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()
                ->back()
                ->with('error', __("Không thể huỷ đơn hàng"));
        } finally {
            $lock->release();
        }
    }
}
