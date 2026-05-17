<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Precinct;
use App\Services\PrecinctService;
use App\Services\TallyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ResultsController extends Controller
{
    public function live(TallyService $tallyService): JsonResponse
    {
        $tally = Cache::get('election_tally');

        if (! $tally) {
            $tally = $tallyService->calculate()->toArray();
            Cache::put('election_tally', $tally, now()->addMinutes(10));
        }

        return response()->json([
            'tally' => $tally,
            'total_votes' => $tallyService->getTotalVotes(),
            'votes_by_position' => $tallyService->getVotesByPosition(),
        ]);
    }

    public function byPosition(string $position, TallyService $tallyService): JsonResponse
    {
        $cacheKey = "election_tally:{$position}";
        $results = Cache::get($cacheKey);

        if (! $results) {
            $results = $tallyService->calculateByPosition($position)->toArray();
            Cache::put($cacheKey, $results, now()->addMinutes(10));
        }

        return response()->json([
            'position' => $position,
            'results' => $results,
        ]);
    }

    public function precinctStatus(PrecinctService $precinctService): JsonResponse
    {
        $precincts = Precinct::select('id', 'precinct_code', 'name', 'region', 'status', 'registered_voters')
            ->orderBy('precinct_code')
            ->get();

        return response()->json([
            'precincts' => $precincts,
            'progress' => $precinctService->getReportingProgress(),
        ]);
    }

    public function precinctDetail(Precinct $precinct): JsonResponse
    {
        $precinct->loadCount(['batches', 'votes']);

        $latestBatch = $precinct->batches()->latest()->first();

        return response()->json([
            'precinct' => $precinct,
            'batches_count' => $precinct->batches_count,
            'votes_count' => $precinct->votes_count,
            'latest_batch' => $latestBatch ? [
                'id' => $latestBatch->id,
                'ballot_count' => $latestBatch->ballot_count,
                'transmission_mode' => $latestBatch->transmission_mode,
                'received_at' => $latestBatch->received_at,
                'status' => $latestBatch->status,
            ] : null,
        ]);
    }
}
