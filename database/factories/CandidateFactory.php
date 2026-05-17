<?php

namespace Database\Factories;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    public function definition(): array
    {
        $positions = ['president', 'vice_president', 'senator', 'party_list'];
        $parties = [
            'Partido ng Bayan',
            'Lakas ng Masa',
            'Alyansa para sa Pagbabago',
            'Pwersa ng Pag-asa',
            'Koalisyon ng Katapatan',
            'Unyon ng Mamamayan',
            'Partido Demokratiko',
            'Buklod ng Pagkakaisa',
        ];

        return [
            'name' => fake()->name(),
            'position' => fake()->randomElement($positions),
            'party' => fake()->randomElement($parties),
            'ballot_number' => fake()->unique()->numberBetween(1, 999),
            'photo_url' => null,
        ];
    }

    public function president(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'president',
        ]);
    }

    public function vicePresident(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'vice_president',
        ]);
    }

    public function senator(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'senator',
        ]);
    }

    public function partyList(): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => 'party_list',
        ]);
    }
}
