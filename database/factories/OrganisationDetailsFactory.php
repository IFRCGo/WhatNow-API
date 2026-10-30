<?php

namespace Database\Factories;

use App\Models\OrganisationDetails;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationDetailsFactory extends Factory
{
    protected $model = OrganisationDetails::class;

    public function definition(): array
    {
        return [
            'language_code' => $this->faker->randomElement(['fr', 'en']),
            'org_name' => $this->faker->domainWord(),
            'attribution_message' => $this->faker->text(100),
        ];
    }
}
