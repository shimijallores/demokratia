<?php

namespace App\Actions\Precinct;

use App\Models\Precinct;
use App\Services\PrecinctService;

class RegeneratePrecinctKeysAction
{
    public function __construct(protected PrecinctService $precinctService) {}

    public function handle(Precinct $precinct): array
    {
        return $this->precinctService->regenerateKeys($precinct);
    }
}
