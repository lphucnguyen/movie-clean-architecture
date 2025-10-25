<?php

namespace App\Domain\Repositories;

use App\Shared\Domain\Repositories\IRepository;

interface IFilmRepository extends IRepository
{
    public function getFilmByCategoryName($categoryName);

    public function getReviews($uuidMovie);

    public function getEpisodes($uuidMovie);

    public function getFilmsByQueryParams(array $queryParams, array $withRelations = []);
}
