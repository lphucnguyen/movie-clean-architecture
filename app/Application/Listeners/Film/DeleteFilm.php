<?php

namespace App\Application\Listeners\Film;

use App\Domain\Events\Film\FilmDeleted;
use App\Domain\Repositories\IFilmRepositoryNeo;

class DeleteFilm
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private IFilmRepositoryNeo $filmRepository,
    )
    {
    }

    /**
     * Handle the event.
     */
    public function handle(FilmDeleted $event): void
    {
        $this->filmRepository->delete($event->filmId);
    }
}
