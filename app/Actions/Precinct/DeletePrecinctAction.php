<?php

namespace App\Actions\Precinct;

use App\Models\Precinct;
use App\Services\PrecinctService;

class DeletePrecinctAction
{
    public function __construct(protected PrecinctService $precinctService) {}

    public function handle(Precinct $precinct): bool
    {
        return $this->precinctService->delete($precinct);
    }
}
