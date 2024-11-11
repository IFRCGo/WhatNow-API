<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\Organisation::class, function (Faker $faker) {
    return [
        'country_code' => $faker->randomElement(['FRA', 'ENG', 'NZL', 'AUS', 'CAN']),
        'org_name' => $faker->domainWord,
        'oid_code' => 'urn:oid:2.49.0.4.190',
    ];
});
