<?php

namespace Database\Factories;

use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        $name = trim($this->faker->randomElement(['North', 'South', 'East', 'West']) . ' ' . $this->faker->randomElement(['', 'Coast', 'Plains', 'Lakes']));

        return [
            'title' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
