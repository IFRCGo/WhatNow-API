<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\WhatNowEntity::class, function (Faker $faker) {
    return [
        'event_type' => $faker->randomElement(['General', 'Earthquake', 'Tornado', 'Wildfire', 'Flood', 'Tsunami', 'Volcano', 'Wind']),
    ];
});
