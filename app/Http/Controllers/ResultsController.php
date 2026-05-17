<?php

namespace App\Http\Controllers;

use App\Models\Precinct;
use App\Services\TallyService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ResultsController extends Controller
{
    public function index(TallyService $tallyService): Response
    {
        $tally = $tallyService->calculate()->toArray();
        Cache::put('election_tally', $tally, now()->addMinutes(10));

        $precincts = Precinct::select('id', 'precinct_code', 'name', 'region', 'status', 'registered_voters')
            ->orderBy('precinct_code')
            ->get();

        return Inertia::render('Results/Index', [
            'tally' => $tally,
            'precincts' => $precincts,
        ]);
    }
}
