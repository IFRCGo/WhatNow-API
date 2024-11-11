<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\WhatNowEntityStage::class, function (Faker $faker) {
    return [
        'language_code' => $faker->randomElement(['fr', 'en']),
        'stage' => $faker->randomElement(\App\Classes\Repositories\WhatNowRepository::EVENT_STAGES),
        'content' => json_encode([
            $faker->text(),
            $faker->text(),
            $faker->text(),
        ]),
    ];
});
