<?php

namespace Database\Factories;

use Faker\Generator as Faker;

$factory->define(\App\Models\Alert::class, function (Faker $faker) {
    return [
        'country_code' => $faker->randomElement(['FRA', 'NZL', 'AUS', 'CAN']),
        'language_code' => $faker->randomElement(['fr', 'en']),
        'event' => $faker->randomElement(['Hurricane Warning', 'Tornado Warning', 'Flood Warning', 'Earthquake Warning', 'Fog Warning', 'Winter Weather Advisory']),
        'headline' => $faker->text(250),
        'description' => $faker->paragraphs(10, true),
        'area_polygon' => json_decode('{"type":"Polygon","coordinates":[[[-180,-90],[-180,-60],[180,-60],[180,-90],[-180,-90]]]}', true),
        'area_description' => 'There is an alert here',
        'type' => $faker->randomElement(['alert', 'update', 'cancel', 'ack', 'error']),
        'status' => $faker->randomElement(['actual', 'system', 'test', 'draft']),
        'scope' => $faker->randomElement(['public', 'restricted', 'private']),
        'category' => $faker->randomElement(['geo', 'met', 'safety', 'security', 'rescue', 'fire', 'health', 'env', 'transport', 'infra', 'CBRNE', 'other']),
        'urgency' => $faker->randomElement(['immediate', 'expected', 'future', 'past', 'unknown']),
        'severity' => $faker->randomElement(['extreme', 'severe', 'moderate', 'minor', 'unknown']),
        'certainty' => $faker->randomElement(['observed', 'likely', 'possible', 'unlikely', 'unknown']),
        'sent_date' => $faker->dateTimeInInterval('-1 minute', '+5 minutes')->format('c'),
        'onset_date' => $faker->dateTimeInInterval('+5 minutes', '+10 minutes')->format('c'),
        'effective_date' => $faker->dateTimeInInterval('+5 minutes', '+10 minutes')->format('c'),
        'expiry_date' => $faker->dateTimeInInterval('+10 minutes', '+12 hours')->format('c'),
    ];
});
