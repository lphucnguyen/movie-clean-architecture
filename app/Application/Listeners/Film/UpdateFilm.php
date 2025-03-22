<?php

namespace App\Application\Listeners\Film;

use App\Domain\Events\Film\FilmUpdated;
use App\Domain\Repositories\IFilmRepositoryNeo;

class UpdateFilm
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
    public function handle(FilmUpdated $event): void
    {
        $fileNameOfBackgoudCover = basename($event->backgroundCover);
        $fileNameOfPoster = basename($event->poster);

        $this->filmRepository->update($event->filmId, [
            'id' => $event->filmId,
            'background_cover' => "/film_background_covers/$fileNameOfBackgoudCover",
            'name' => $event->name,
            'overview' => $event->overview,
            'poster' => "/film_posters/$fileNameOfPoster"
        ]);
    }
}
