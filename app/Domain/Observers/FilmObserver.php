<?php

namespace App\Domain\Observers;

use App\Domain\Events\Film\FilmCreated;
use App\Domain\Events\Film\FilmDeleted;
use App\Domain\Events\Film\FilmUpdated;

use App\Domain\Models\Film;
use App\Shared\Domain\Concerns\HasUpdatedAttributes;

class FilmObserver
{
    use HasUpdatedAttributes;

    public function creating(Film $model)
    {
        $model->id = str()->uuid();
    }

    public function created(Film $model)
    {
        event(new FilmCreated($model->id, $model->name, $model->overview, $model->poster, $model->background_cover));
    }

    public function updated(Film $model)
    {
        if (!$this->isUpdate($model)) {
            return;
        }

        event(new FilmUpdated($model->id, $model->name, $model->overview, $model->poster, $model->background_cover));
    }

    public function deleted(Film $model)
    {
        event(new FilmDeleted($model->id));
    }
}
