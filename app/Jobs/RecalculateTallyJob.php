<?php

namespace App\Jobs;

use App\Services\TallyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class RecalculateTallyJob implements ShouldQueue
{
    use Queueable;

    public function handle(TallyService $tallyService): void
    {
        $tally = $tallyService->calculate();

        Cache::put('election_tally', $tally->toArray(), now()->addMinutes(10));

        foreach ($tally as $position => $results) {
            Cache::put("election_tally:{$position}", $results->toArray(), now()->addMinutes(10));
        }
    }
}
