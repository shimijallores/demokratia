<?php

namespace Database\Factories;

use App\Models\Precinct;
use App\Models\UploadSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UploadSession>
 */
class UploadSessionFactory extends Factory
{
    public function definition(): array
    {
        $totalChunks = fake()->numberBetween(3, 10);

        return [
            'id' => fake()->uuid(),
            'precinct_id' => Precinct::factory(),
            'batch_id' => fake()->optional(0.5)->uuid(),
            'total_chunks' => $totalChunks,
            'received_chunks' => fake()->randomElements(range(0, $totalChunks - 1), fake()->numberBetween(0, $totalChunks)),
            'expires_at' => now()->addHours(4),
            'status' => fake()->randomElement(['active', 'finalized', 'expired']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expires_at' => now()->addHours(4),
        ]);
    }

    public function finalized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'finalized',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subHours(1),
        ]);
    }
}
