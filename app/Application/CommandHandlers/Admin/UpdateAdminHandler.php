<?php

namespace App\Application\CommandHandlers\Admin;

use App\Application\Commands\Admin\UpdateAdminCommand;
use App\Domain\Repositories\IAdminRepository;
use Illuminate\Support\Facades\DB;

class UpdateAdminHandler
{
    public function __construct(
        private IAdminRepository $repository
    ) {
    }

    public function handle(UpdateAdminCommand $command)
    {
        try {
            DB::beginTransaction();

            $admin = $this->repository->getWithLock($command->uuid);
            $data = $command->data;

            $parts = explode("/", $data->avatar);
            $file = implode('/', array_slice($parts, -2));
            $data->avatar = $file;

            if ($data->password) {
                $data->password = bcrypt($data->password);
            } else {
                unset($data->password);
            }

            $admin->update($data->toArray());

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
