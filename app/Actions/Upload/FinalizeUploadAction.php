<?php

namespace App\Actions\Upload;

use App\Jobs\RecalculateTallyJob;
use App\Models\Batch;
use App\Models\Precinct;
use App\Models\UploadSession;
use App\Services\EncryptionService;
use App\Services\TallyService;
use App\Services\UploadSessionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinalizeUploadAction
{
    public function __construct(
        protected UploadSessionService $uploadSessionService,
        protected EncryptionService $encryptionService,
        protected TallyService $tallyService,
    ) {}

    public function handle(UploadSession $session, string $checksum): Batch
    {
        $reassembled = $this->uploadSessionService->finalize($session);

        $precinct = $session->precinct;
        $decrypted = $this->encryptionService->decrypt($precinct, $reassembled);

        if (! $this->encryptionService->validateChecksum($decrypted, $checksum)) {
            throw new \RuntimeException('Checksum validation failed');
        }

        $payload = json_decode($decrypted, true);

        if (! is_array($payload) || empty($payload)) {
            throw new \RuntimeException('Invalid payload format');
        }

        return DB::transaction(function () use ($session, $precinct, $payload, $checksum) {
            $batchId = $session->batch_id;
            $ballotCount = count($payload);

            $batch = $this->uploadSessionService->createBatch(
                $precinct,
                $batchId,
                $ballotCount,
                $checksum,
                'stream',
            );

            $batch->update([
                'status' => 'processing',
                'received_at' => now(),
            ]);

            $this->tallyService->processBatch($batch, $payload);

            $batch->update(['status' => 'complete']);

            $this->updatePrecinctStatus($precinct);

            Cache::forget('election_tally');

            RecalculateTallyJob::dispatch();

            return $batch;
        });
    }

    protected function updatePrecinctStatus(Precinct $precinct): void
    {
        if ($precinct->status === 'pending') {
            $precinct->update(['status' => 'transmitting']);
        }
    }
}
