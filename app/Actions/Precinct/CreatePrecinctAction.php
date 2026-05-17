<?php

namespace App\Actions\Precinct;

use App\Models\Precinct;
use App\Services\PrecinctService;

class CreatePrecinctAction
{
    public function __construct(protected PrecinctService $precinctService)
    {
    }

    public function handle(array $data): Precinct
    {
        return $this->precinctService->create($data);
    }
}
