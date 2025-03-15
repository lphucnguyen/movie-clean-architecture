<?php

namespace App\Domain\Observers;

use App\Domain\Events\Rating\RatingCreated;
use App\Domain\Events\Rating\RatingDeleted;
use App\Domain\Events\Rating\RatingUpdated;

use App\Domain\Models\Rating;
use App\Shared\Domain\Concerns\HasUpdatedAttributes;

class RatingFilmObserver
{
    use HasUpdatedAttributes;

    public function created(Rating $model)
    {
        event(new RatingCreated($model->film_id, $model->user_id, $model->rating));
    }

    public function updated(Rating $model)
    {
        if (!$this->isUpdate($model)) {
            return;
        }

        event(new RatingUpdated($model->film_id, $model->user_id, $model->rating));
    }

    public function deleted(Rating $model)
    {
        event(new RatingDeleted($model->film_id, $model->user_id));
    }
}
