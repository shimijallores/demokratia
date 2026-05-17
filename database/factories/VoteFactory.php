<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Candidate;
use App\Models\Precinct;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    public function definition(): array
    {
        $candidate = Candidate::factory()->create();

        return [
            'batch_id' => Batch::factory(),
            'candidate_id' => $candidate->id,
            'precinct_id' => Precinct::factory(),
            'position' => $candidate->position,
        ];
    }
}
