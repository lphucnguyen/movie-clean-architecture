<?php

namespace App\Infrastructure\Repositories;


use App\Domain\Repositories\IReviewRepository;
use App\Domain\Models\Review;
use App\Shared\Infrastructure\Repositories\BaseRepository;
use Illuminate\Pipeline\Pipeline;

class ReviewRepository extends BaseRepository implements IReviewRepository
{
    public function __construct(
        Review $model
    ) {
        parent::__construct($model);
    }

    public function getReviewByQueryParams(array $queryParams, array $withRelations = [])
    {
        $query = $this->makeQuery();

        return app(Pipeline::class)
            ->send($query)
            ->through([
                new \App\Infrastructure\QueryFilters\Review\FilterByUser($queryParams),
                new \App\Infrastructure\QueryFilters\Review\FilterByFilm($queryParams),
            ])
            ->thenReturn()
            ->with($withRelations)
            ->latest()
            ->paginate(config('app.perPage'));
    }
}
