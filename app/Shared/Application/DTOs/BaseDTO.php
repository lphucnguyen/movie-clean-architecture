<?php

namespace App\Shared\Application\DTOs;

use App\Shared\Application\Concerns\ConvertsToArray;
use App\Shared\Application\Concerns\CreateableFromSource;

class BaseDTO
{
    use CreateableFromSource;
    use ConvertsToArray;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
