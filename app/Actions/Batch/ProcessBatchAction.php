<?php

namespace App\Actions\Batch;

use App\Jobs\RecalculateTallyJob;
use App\Models\Batch;
use App\Services\TallyService;

class ProcessBatchAction
{
    public function __construct(protected TallyService $tallyService)
    {
    }

    public function handle(Batch $batch, array $ballots): Batch
    {
        $batch->update(['status' => 'processing']);

        $this->tallyService->processBatch($batch, $ballots);

        $batch->update(['status' => 'complete']);

        RecalculateTallyJob::dispatch();

        return $batch;
    }
}
