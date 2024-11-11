<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\Region::class, function (Faker $faker) {
    $name = trim($faker->randomElement(['North', 'South', 'East', 'West']) . ' ' . $faker->randomElement(['', 'Coast', 'Plains', 'Lakes']));

    return [
        'title' => $name,
        'slug' => str_slug($name),
    ];
});
