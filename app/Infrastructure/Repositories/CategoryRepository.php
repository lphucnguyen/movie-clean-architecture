<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Category;

use App\Domain\Repositories\ICategoryRepository;
use App\Shared\Infrastructure\Repositories\BaseRepository;

class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    public function __construct(
        private Category $model
    ) {
        parent::__construct($model);
    }

    public function getCategoriesByQueryParams(array $queryParams)
    {
        $query = $this->makeQuery();

        return $query->where(function ($query) use ($queryParams) {
            $query->when($queryParams['searchKey'], function ($q) use ($queryParams) {
                return $q->where('name', 'like', '%' . $queryParams['searchKey'] . '%');
            });
        })->latest()->paginate(config('app.perPage'));
    }

    public function getLatestCategoriesWithFilms(int $limitCategory, int $limitFilm)
    {
        return $this->model->with(['films' => function ($query) use ($limitFilm) {
            $query->limit($limitFilm);
        }])->limit($limitCategory)->get();
    }
}
