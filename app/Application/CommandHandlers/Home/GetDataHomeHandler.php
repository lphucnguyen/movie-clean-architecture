<?php

namespace App\Application\CommandHandlers\Home;

use App\Application\Commands\Home\GetDataHomeCommand;
use App\Application\DTOs\Film\GetMoviesDTO;
use App\Application\DTOs\Home\GetDataHomeReponse;
use App\Domain\Repositories\ICategoryRepository;
use App\Domain\Repositories\IFilmRepository;
use App\Domain\Repositories\IFilmRepositoryNeo;

class GetDataHomeHandler
{
    private int $MAX_MOVIE_PER_CATEGORY = 10;

    public function __construct(
        private IFilmRepository $filmRepository,
        private ICategoryRepository $categoryRepository,
        private IFilmRepositoryNeo $filmRepositoryNeo
    ) {
    }

    public function handle(GetDataHomeCommand $command)
    {
        $getMoviesDTO = new GetMoviesDTO();
        $sliderFilms = $this->filmRepository->getFilmsByQueryParams($getMoviesDTO->toArray(), [
            'categories',
            'ratings',
        ])->getCollection();
        $categoryWithFilms = $this->categoryRepository->getLatestCategoriesWithFilms(
            config('app.perPage'),
            $this->MAX_MOVIE_PER_CATEGORY
        );
        $suggestedFilms = [];
        $ratings = [];
        $user = auth()->guard('web')->user();

        if ($user != null) {
            $films = $this->filmRepositoryNeo->getRecommendByUser($user->id);

            $suggestedFilms = $films['suggestedFilms'];
            $ratings = $films['ratings'];
        }

        return new GetDataHomeReponse([
            'sliderFilms' => $sliderFilms,
            'categoryFilms' => $categoryWithFilms,
            'suggestedFilms' => $suggestedFilms,
            'ratings' => $ratings,
            'user' => $user,
        ]);
    }
}
