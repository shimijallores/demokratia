<?php

namespace App\Actions\Precinct;

use App\Services\PrecinctService;

class CreatePrecinctAction
{
    public function __construct(protected PrecinctService $precinctService) {}

    public function handle(array $data): array
    {
        return $this->precinctService->create($data);
    }
}
