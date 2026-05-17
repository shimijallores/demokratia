<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Precinct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => fake()->uuid(),
            'precinct_id' => Precinct::factory(),
            'ballot_count' => fake()->numberBetween(50, 500),
            'transmission_mode' => fake()->randomElement(['stream', 'flashdrive']),
            'checksum' => fake()->sha256(),
            'received_at' => fake()->optional(0.7)->dateTime(),
            'status' => fake()->randomElement(['pending', 'processing', 'complete', 'failed']),
        ];
    }

    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'complete',
            'received_at' => now(),
        ]);
    }

    public function stream(): static
    {
        return $this->state(fn (array $attributes) => [
            'transmission_mode' => 'stream',
        ]);
    }

    public function flashdrive(): static
    {
        return $this->state(fn (array $attributes) => [
            'transmission_mode' => 'flashdrive',
        ]);
    }
}
