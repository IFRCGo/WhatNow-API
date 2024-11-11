<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\WhatNowEntityTranslation::class, function (Faker $faker) {
    return [
        'language_code' => $faker->randomElement(['fr', 'en']),
        'title' => $faker->text(),
        'web_url' => $faker->url,
        'description' => $faker->text(),
    ];
});
