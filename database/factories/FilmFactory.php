<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Domain\Models\Film;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;

$factory->define(Film::class, function (Faker $faker) {
    $directory = 'film_background_covers';
    $files = Storage::allFiles($directory);
    $randomFileBackground = $files[rand(0, count($files) - 1)];

    $directory = 'film_posters';
    $files = Storage::allFiles($directory);
    $randomFilePoster = $files[rand(0, count($files) - 1)];

    return [
        'id'   => str()->uuid(),
        'name' => $faker->unique()->name,
        'year' => $faker->numberBetween(1996, 2020),
        'overview' => $faker->text('255'),
        'background_cover' => 'film_background_covers/' . $randomFileBackground,
        'poster' => 'film_posters/' . $randomFilePoster
    ];
});
