<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Rating;

use App\Domain\Repositories\IRatingRepository;
use App\Shared\Infrastructure\Repositories\BaseRepository;
use Illuminate\Pipeline\Pipeline;

class RatingRepository extends BaseRepository implements IRatingRepository
{
    public function __construct(
        Rating $model
    ) {
        parent::__construct($model);
    }

    public function getRatingsByQueryParams(array $queryParams, array $withRelations = [])
    {
        $query = $this->makeQuery();

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new \App\Infrastructure\QueryFilters\Rating\FilterByUser($queryParams),
                new \App\Infrastructure\QueryFilters\Rating\FilterByFilm($queryParams),
                new \App\Infrastructure\QueryFilters\Rating\FilterByRating($queryParams),
            ])
            ->thenReturn()
            ->with($withRelations)
            ->latest()
            ->paginate(config('app.perPage'));
    }
}
