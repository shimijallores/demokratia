<?php

namespace Database\Factories;

use App\Models\Precinct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Precinct>
 */
class PrecinctFactory extends Factory
{
    public function definition(): array
    {
        $regions = ['NCR', 'Region I', 'Region III', 'Region IV-A', 'Region VII', 'Region XI', 'Region XII'];
        $region = fake()->randomElement($regions);

        return [
            'precinct_code' => strtoupper(fake()->bothify('????-###')),
            'name' => fake()->city() . ' Precinct ' . fake()->unique()->numberBetween(1, 999),
            'region' => $region,
            'province' => fake()->city(),
            'municipality' => fake()->city(),
            'barangay' => 'Barangay ' . fake()->lastName(),
            'registered_voters' => fake()->numberBetween(500, 3000),
            'api_key_hash' => bcrypt(fake()->unique()->lexify(64)),
            'aes_key_encrypted' => encrypt(fake()->lexify(32)),
            'status' => fake()->randomElement(['pending', 'transmitting', 'partial', 'complete', 'error']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'complete',
        ]);
    }

    public function transmitting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'transmitting',
        ]);
    }

    public function error(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'error',
        ]);
    }
}
