<?php

namespace App\Actions\Upload;

use App\Models\Precinct;
use App\Models\UploadSession;
use App\Services\UploadSessionService;

class InitUploadSessionAction
{
    public function __construct(protected UploadSessionService $uploadSessionService) {}

    public function handle(Precinct $precinct, array $data): UploadSession
    {
        return $this->uploadSessionService->init($precinct, $data);
    }
}
