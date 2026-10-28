<?php

namespace Database\Factories;

use App\Models\WhatNowEntityTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class WhatNowEntityTranslationFactory extends Factory
{
    protected $model = WhatNowEntityTranslation::class;

    public function definition(): array
    {
        return [
            'language_code' => $this->faker->randomElement(['fr', 'en']),
            'title' => $this->faker->text(),
            'web_url' => $this->faker->url(),
            'description' => $this->faker->text(),
        ];
    }
}
