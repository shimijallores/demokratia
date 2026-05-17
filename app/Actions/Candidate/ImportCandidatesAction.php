<?php

namespace App\Actions\Candidate;

use App\Models\Candidate;
use App\Services\CandidateService;

class ImportCandidatesAction
{
    public function __construct(protected CandidateService $candidateService)
    {
    }

    public function handle(array $candidates): array
    {
        return $this->candidateService->import($candidates);
    }
}
