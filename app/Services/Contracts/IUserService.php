<?php

namespace App\Services\Contracts;

use App\Services\IService;

interface IUserService extends IService
{
    public function getTransactions($uuid);
}
