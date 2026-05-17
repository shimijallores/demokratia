<?php

namespace App\Actions\Upload;

use App\Jobs\RecalculateTallyJob;
use App\Models\Batch;
use App\Models\Precinct;
use App\Models\UploadSession;
use App\Services\EncryptionService;
use App\Services\TallyService;
use App\Services\UploadSessionService;
use Illuminate\Support\Facades\DB;

class FinalizeUploadAction
{
    public function __construct(
        protected UploadSessionService $uploadSessionService,
        protected EncryptionService $encryptionService,
        protected TallyService $tallyService,
    ) {
    }

    public function handle(UploadSession $session, string $checksum): Batch
    {
        $reassembled = $this->uploadSessionService->finalize($session);

        $precinct = $session->precinct;
        $decrypted = $this->encryptionService->decrypt($precinct, $reassembled);

        if (!$this->encryptionService->validateChecksum($decrypted, $checksum)) {
            throw new \RuntimeException('Checksum validation failed');
        }

        $payload = json_decode($decrypted, true);

        if (!$payload || !isset($payload['batch_id'])) {
            throw new \RuntimeException('Invalid payload format');
        }

        $existingBatch = Batch::where('id', $payload['batch_id'])->first();

        if ($existingBatch) {
            throw new \RuntimeException('Duplicate batch_id: ' . $payload['batch_id']);
        }

        return DB::transaction(function () use ($session, $precinct, $payload, $checksum) {
            $batch = $this->uploadSessionService->createBatch(
                $precinct,
                $payload['batch_id'],
                $payload['ballot_count'],
                $checksum,
                'stream',
            );

            $batch->update([
                'status' => 'processing',
                'received_at' => now(),
            ]);

            $this->tallyService->processBatch($batch, $payload['ballots']);

            $batch->update(['status' => 'complete']);

            $this->updatePrecinctStatus($precinct);

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
