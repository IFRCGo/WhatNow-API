<?php

namespace Database\Factories;

use App\Models\RegionTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionTranslationFactory extends Factory
{
    protected $model = RegionTranslation::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->text(20),
            'language_code' => $this->faker->randomElement(['fr', 'en', 'es']),
            'description' => $this->faker->text(300),
        ];
    }
}
