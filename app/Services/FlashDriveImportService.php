<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Precinct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FlashDriveImportService
{
    public function __construct(
        protected EncryptionService $encryptionService,
        protected UploadSessionService $uploadSessionService,
        protected TallyService $tallyService,
    ) {}

    public function import(Precinct $precinct, UploadedFile $file): array
    {
        $content = Storage::disk('local')->putFileAs('imports', $file, $file->getClientOriginalName());
        $binary = Storage::disk('local')->get($content);

        $iv = substr($binary, 0, 12);
        $ciphertext = substr($binary, 12);

        $aesKey = decrypt($precinct->aes_key_encrypted);

        $decrypted = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            hex2bin($aesKey),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
        );

        if ($decrypted === false) {
            Storage::disk('local')->delete($content);

            throw new \RuntimeException('Failed to decrypt .acm file');
        }

        $payload = json_decode($decrypted, true);

        if (! $payload || ! isset($payload['batch_id'])) {
            Storage::disk('local')->delete($content);

            throw new \RuntimeException('Invalid .acm file format');
        }

        $existingBatch = Batch::where('id', $payload['batch_id'])->first();

        if ($existingBatch) {
            Storage::disk('local')->delete($content);

            throw new \RuntimeException('Duplicate batch_id: '.$payload['batch_id']);
        }

        $computedChecksum = $this->encryptionService->computeChecksum(json_encode($payload['ballots']));

        if (! hash_equals($computedChecksum, $payload['checksum'])) {
            Storage::disk('local')->delete($content);

            throw new \RuntimeException('Checksum validation failed');
        }

        $batch = $this->uploadSessionService->createBatch(
            $precinct,
            $payload['batch_id'],
            $payload['ballot_count'],
            $payload['checksum'],
            'flashdrive',
        );

        $batch->update([
            'status' => 'processing',
            'received_at' => now(),
        ]);

        $this->tallyService->processBatch($batch, $payload['ballots']);

        $batch->update(['status' => 'complete']);

        $precinct->update(['status' => 'complete']);

        Storage::disk('local')->delete($content);

        return [
            'batch_id' => $batch->id,
            'ballot_count' => $batch->ballot_count,
            'status' => $batch->status,
        ];
    }
}
