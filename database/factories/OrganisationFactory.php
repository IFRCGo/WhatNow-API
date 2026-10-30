<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationFactory extends Factory
{
    protected $model = Organisation::class;

    public function definition(): array
    {
        return [
            'country_code' => $this->faker->randomElement(['FRA', 'ENG', 'NZL', 'AUS', 'CAN']),
            'org_name' => $this->faker->domainWord(),
            'oid_code' => 'urn:oid:2.49.0.4.190',
        ];
    }
}
