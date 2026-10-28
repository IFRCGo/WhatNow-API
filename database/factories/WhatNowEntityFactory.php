<?php

namespace Database\Factories;

use App\Models\WhatNowEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhatNowEntityFactory extends Factory
{
    protected $model = WhatNowEntity::class;

    public function definition(): array
    {
        return [
            'event_type' => $this->faker->randomElement(['General', 'Earthquake', 'Tornado', 'Wildfire', 'Flood', 'Tsunami', 'Volcano', 'Wind']),
        ];
    }
}
