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
        $tally = Vote::selectRaw('candidate_id, position, COUNT(*) as vote_count')
            ->groupBy('candidate_id', 'position')
            ->get();

        $candidates = Candidate::whereIn('id', $tally->pluck('candidate_id'))->get()->keyBy('id');

        $totalVotes = Vote::count();

        return $tally->map(function ($row) use ($candidates, $totalVotes) {
            $candidate = $candidates->get($row->candidate_id);

            return [
                'candidate_id' => $row->candidate_id,
                'name' => $candidate?->name ?? 'Unknown',
                'party' => $candidate?->party ?? 'Independent',
                'position' => $row->position,
                'ballot_number' => $candidate?->ballot_number ?? '',
                'photo_url' => $candidate?->photo_url,
                'vote_count' => $row->vote_count,
                'percentage' => $totalVotes > 0 ? round(($row->vote_count / $totalVotes) * 100, 2) : 0,
            ];
        })
            ->groupBy('position')
            ->map(function ($group) {
                return $group->sortByDesc('vote_count')->values();
            });
    }

    public function calculateByPosition(string $position): Collection
    {
        $tally = Vote::selectRaw('candidate_id, COUNT(*) as vote_count')
            ->where('position', $position)
            ->groupBy('candidate_id')
            ->get();

        $candidates = Candidate::whereIn('id', $tally->pluck('candidate_id'))->get()->keyBy('id');

        $positionTotal = Vote::where('position', $position)->count();

        return $tally->map(function ($row) use ($candidates, $positionTotal) {
            $candidate = $candidates->get($row->candidate_id);

            return [
                'candidate_id' => $row->candidate_id,
                'name' => $candidate?->name ?? 'Unknown',
                'party' => $candidate?->party ?? 'Independent',
                'position' => $position,
                'ballot_number' => $candidate?->ballot_number ?? '',
                'photo_url' => $candidate?->photo_url,
                'vote_count' => $row->vote_count,
                'percentage' => $positionTotal > 0 ? round(($row->vote_count / $positionTotal) * 100, 2) : 0,
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
