<?php

namespace App\Shared\Application\Concerns;

trait ConvertsToArray
{
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
