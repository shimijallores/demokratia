<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TallyService
{
    public function calculate(): Collection
    {
        $voteTally = Vote::selectRaw('candidate_id, position, COUNT(*) as vote_count')
            ->groupBy('candidate_id', 'position')
            ->get()
            ->keyBy(fn ($row) => $row->candidate_id.'-'.$row->position);

        $candidates = Candidate::orderBy('position')->orderBy('ballot_number')->get();

        $totalVotes = Vote::count();

        $results = $candidates->map(function ($candidate) use ($voteTally, $totalVotes) {
            $key = $candidate->id.'-'.$candidate->position;
            $voteRow = $voteTally->get($key);
            $voteCount = $voteRow?->vote_count ?? 0;

            return [
                'candidate_id' => $candidate->id,
                'name' => $candidate->name,
                'party' => $candidate->party ?? 'Independent',
                'position' => $candidate->position,
                'ballot_number' => $candidate->ballot_number,
                'photo_url' => $candidate->photo_url,
                'vote_count' => $voteCount,
                'percentage' => $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0,
            ];
        })
            ->groupBy('position')
            ->map(function ($group) {
                return $group->sortByDesc('vote_count')->values();
            });

        foreach (['president', 'vice_president', 'senator', 'party_list'] as $position) {
            if (! $results->has($position)) {
                $results[$position] = [];
            }
        }

        return $results;
    }

    public function calculateByPosition(string $position): Collection
    {
        $voteTally = Vote::selectRaw('candidate_id, COUNT(*) as vote_count')
            ->where('position', $position)
            ->groupBy('candidate_id')
            ->get()
            ->keyBy('candidate_id');

        $candidates = Candidate::where('position', $position)
            ->orderBy('ballot_number')
            ->get();

        $positionTotal = Vote::where('position', $position)->count();

        return $candidates->map(function ($candidate) use ($voteTally, $positionTotal) {
            $voteCount = $voteTally->get($candidate->id)?->vote_count ?? 0;

            return [
                'candidate_id' => $candidate->id,
                'name' => $candidate->name,
                'party' => $candidate->party ?? 'Independent',
                'position' => $position,
                'ballot_number' => $candidate->ballot_number,
                'photo_url' => $candidate->photo_url,
                'vote_count' => $voteCount,
                'percentage' => $positionTotal > 0 ? round(($voteCount / $positionTotal) * 100, 2) : 0,
            ];
        })->sortByDesc('vote_count')->values();
    }

    public function processBatch(Batch $batch, array $ballots): void
    {
        $votesToInsert = [];

        foreach ($ballots as $ballot) {
            foreach ($ballot['selections'] as $selection) {
                $candidateIds = is_array($selection['candidate_id'])
                    ? $selection['candidate_id']
                    : [$selection['candidate_id']];

                foreach ($candidateIds as $candidateId) {
                    Candidate::firstOrCreate(
                        ['id' => (int) $candidateId],
                        [
                            'name' => 'Candidate #'.$candidateId,
                            'position' => $selection['position'],
                            'party' => 'Unknown',
                            'ballot_number' => (string) $candidateId,
                        ]
                    );

                    $votesToInsert[] = [
                        'batch_id' => $batch->id,
                        'candidate_id' => (int) $candidateId,
                        'precinct_id' => $batch->precinct_id,
                        'position' => $selection['position'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (! empty($votesToInsert)) {
            DB::table('votes')->insert($votesToInsert);
        }
    }

    public function getTotalVotes(): int
    {
        return Vote::count();
    }

    public function getVotesByPosition(): array
    {
        return Vote::selectRaw('position, COUNT(*) as count')
            ->groupBy('position')
            ->pluck('count', 'position')
            ->toArray();
    }
}
