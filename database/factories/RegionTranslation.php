<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\RegionTranslation::class, function (Faker $faker) {
    return [
        'title' => $faker->text(20),
        'language_code' => $faker->randomElement(['fr', 'en', 'es']),
        'description' => $faker->text(300),
    ];
});
