<?php

namespace App\Actions\Precinct;

use App\Models\Precinct;
use App\Services\PrecinctService;

class UpdatePrecinctAction
{
    public function __construct(protected PrecinctService $precinctService) {}

    public function handle(Precinct $precinct, array $data): Precinct
    {
        return $this->precinctService->update($precinct, $data);
    }
}
