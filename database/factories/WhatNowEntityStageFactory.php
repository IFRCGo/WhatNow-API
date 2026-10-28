<?php

namespace Database\Factories;

use App\Classes\Repositories\WhatNowRepository;
use App\Models\WhatNowEntityStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhatNowEntityStageFactory extends Factory
{
    protected $model = WhatNowEntityStage::class;

    public function definition(): array
    {
        return [
            'language_code' => $this->faker->randomElement(['fr', 'en']),
            'stage' => $this->faker->randomElement(WhatNowRepository::EVENT_STAGES),
        ];
    }
}
