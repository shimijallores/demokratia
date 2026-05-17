<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function index(): Response
    {
        $totalVotes = Vote::count();
        $totalBatches = Batch::where('status', 'complete')->count();

        return Inertia::render('Export/Index', [
            'totalVotes' => $totalVotes,
            'totalBatches' => $totalBatches,
        ]);
    }

    public function csv(): StreamedResponse
    {
        $tally = Cache::get('election_tally');

        if (! $tally) {
            abort(404, 'No tally data available.');
        }

        $response = new StreamedResponse(function () use ($tally) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Position', 'Rank', 'Candidate', 'Party', 'Votes', 'Percentage']);

            foreach ($tally as $position => $results) {
                foreach ($results as $rank => $result) {
                    fputcsv($handle, [
                        $position,
                        $rank + 1,
                        $result['name'],
                        $result['party'],
                        $result['vote_count'],
                        $result['percentage'].'%',
                    ]);
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="election_results.csv"');

        return $response;
    }

    public function json(): JsonResponse
    {
        $tally = Cache::get('election_tally');

        if (! $tally) {
            return response()->json(['message' => 'No tally data available.'], 404);
        }

        return response()->json([
            'tally' => $tally,
            'exported_at' => now()->toIso8601String(),
        ]);
    }
}
