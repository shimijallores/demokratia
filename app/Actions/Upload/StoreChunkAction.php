<?php

namespace App\Actions\Upload;

use App\Models\UploadSession;
use App\Services\UploadSessionService;

class StoreChunkAction
{
    public function __construct(protected UploadSessionService $uploadSessionService) {}

    public function handle(UploadSession $session, int $chunkIndex, string $data): void
    {
        $this->uploadSessionService->storeChunk($session, $chunkIndex, $data);
    }
}
