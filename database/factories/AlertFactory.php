<?php

namespace Database\Factories;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'country_code' => $this->faker->randomElement(['FRA', 'NZL', 'AUS', 'CAN']),
            'language_code' => $this->faker->randomElement(['fr', 'en']),
            'event' => $this->faker->randomElement(['Hurricane Warning', 'Tornado Warning', 'Flood Warning', 'Earthquake Warning', 'Fog Warning', 'Winter Weather Advisory']),
            'headline' => $this->faker->text(250),
            'description' => $this->faker->paragraphs(10, true),
            'area_polygon' => json_decode('{"type":"Polygon","coordinates":[[[-180,-90],[-180,-60],[180,-60],[180,-90],[-180,-90]]]}', true),
            'area_description' => 'There is an alert here',
            'type' => $this->faker->randomElement(['alert', 'update', 'cancel', 'ack', 'error']),
            'status' => $this->faker->randomElement(['actual', 'system', 'test', 'draft']),
            'scope' => $this->faker->randomElement(['public', 'restricted', 'private']),
            'category' => $this->faker->randomElement(['geo', 'met', 'safety', 'security', 'rescue', 'fire', 'health', 'env', 'transport', 'infra', 'CBRNE', 'other']),
            'urgency' => $this->faker->randomElement(['immediate', 'expected', 'future', 'past', 'unknown']),
            'severity' => $this->faker->randomElement(['extreme', 'severe', 'moderate', 'minor', 'unknown']),
            'certainty' => $this->faker->randomElement(['observed', 'likely', 'possible', 'unlikely', 'unknown']),
            'sent_date' => $this->faker->dateTimeInInterval('-1 minute', '+5 minutes')->format('c'),
            'onset_date' => $this->faker->dateTimeInInterval('+5 minutes', '+10 minutes')->format('c'),
            'effective_date' => $this->faker->dateTimeInInterval('+5 minutes', '+10 minutes')->format('c'),
            'expiry_date' => $this->faker->dateTimeInInterval('+10 minutes', '+12 hours')->format('c'),
        ];
    }
}
