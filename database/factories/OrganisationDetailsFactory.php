<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\OrganisationDetails::class, function (Faker $faker) {
    return [
        'language_code' => $faker->randomElement(['fr', 'en']),
        'org_name' => $faker->domainWord,
        'attribution_message' => $faker->text(100),
    ];
});
