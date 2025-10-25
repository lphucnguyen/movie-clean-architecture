<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Film;

use App\Domain\Repositories\IFilmRepository;
use App\Shared\Infrastructure\Repositories\BaseRepository;
use Illuminate\Pipeline\Pipeline;

class FilmRepository extends BaseRepository implements IFilmRepository
{
    public function __construct(
        Film $model
    ) {
        parent::__construct($model);
    }

    public function getFilmByCategoryName($categoryName)
    {
        return $this->model->where(function ($query) use ($categoryName) {
            $query->when($categoryName, function ($query) use ($categoryName) {
                return $query->whereHas('categories', function ($query2) use ($categoryName) {
                    return $query2->whereIn('name', (array)$categoryName);
                });
            });
        })->latest()->paginate(config('app.perPage'));
    }

    public function getReviews($uuid)
    {
        return $this->get($uuid)->reviews()->latest()->paginate(config('app.perPage'));
    }

    public function getEpisodes($uuid)
    {
        return $this->get($uuid)->episodes()->latest()->get();
    }

    public function getFilmsByQueryParams(array $queryParams, array $withRelations = [])
    {
        $query = $this->makeQuery();

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new \App\Infrastructure\QueryFilters\Film\FilterByName($queryParams),
                new \App\Infrastructure\QueryFilters\Film\FilterByCategory($queryParams),
                new \App\Infrastructure\QueryFilters\Film\FilterByActor($queryParams),
                new \App\Infrastructure\QueryFilters\Film\FilterByFavorite($queryParams),
            ])
            ->thenReturn()
            ->with($withRelations)
            ->latest()
            ->paginate(config('app.perPage'));
    }
}
