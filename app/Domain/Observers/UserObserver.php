<?php

namespace App\Domain\Observers;

use App\Domain\Events\User\UserCreated;
use App\Domain\Events\User\UserDeleted;
use App\Domain\Events\User\UserUpdated;

use App\Domain\Models\User;
use App\Shared\Domain\Concerns\HasUpdatedAttributes;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    use HasUpdatedAttributes;

    public function creating(User $model)
    {
        $model->id = str()->uuid();
    }

    public function created(User $model)
    {
        event(new UserCreated($model->id, $model->username, $model->email, $model->first_name, $model->last_name));
    }

    public function updating(User $model)
    {
        if ($model->isDirty('avatar') && $model->getRawOriginal('avatar') !== null) {
            Storage::delete($model->getRawOriginal('avatar'));
        }
    }

    public function updated(User $model)
    {
        if (!$this->isUpdate($model)) {
            return;
        }

        event(new UserUpdated($model->id, $model->first_name, $model->last_name));
    }

    public function deleting(User $model)
    {
        if ($model->getRawOriginal('avatar') !== null) {
            Storage::delete($model->getRawOriginal('avatar'));
        }
    }

    public function deleted(User $model)
    {
        event(new UserDeleted($model->id));
    }
}
