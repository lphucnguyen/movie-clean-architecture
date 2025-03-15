<?php

namespace App\Domain\Observers;

use App\Domain\Events\Category\CategoryCreated;
use App\Domain\Events\Category\CategoryDeleted;
use App\Domain\Events\Category\CategoryUpdated;

use App\Domain\Models\Category;
use App\Shared\Domain\Concerns\HasUpdatedAttributes;

class CategoryObserver
{
    use HasUpdatedAttributes;

    public function created(Category $model)
    {
        event(new CategoryCreated($model->id, $model->name));
    }

    public function updated(Category $model)
    {
        if (!$this->isUpdate($model)) {
            return;
        }

        event(new CategoryUpdated($model->id, $model->name));
    }

    public function deleted(Category $model)
    {
        event(new CategoryDeleted($model->id));
    }
}
