<?php

namespace App\Domain\Observers;

use App\Domain\Models\Actor;
use Illuminate\Support\Facades\Storage;

class ActorObserver
{
    public function creating(Actor $model)
    {
        $model->id = str()->uuid();
    }
}
