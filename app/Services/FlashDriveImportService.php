<?php

namespace App\Services;

use App\Models\Precinct;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
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

        $ballots = json_decode($decrypted, true);

        if (! is_array($ballots) || empty($ballots)) {
            Storage::disk('local')->delete($content);

            throw new \RuntimeException('Invalid .acm file format');
        }

        $batchId = $ballots[0]['ballot_id'] ?? str()->uuid();
        $computedChecksum = $this->encryptionService->computeChecksum($decrypted);

        $batch = $this->uploadSessionService->createBatch(
            $precinct,
            $batchId,
            count($ballots),
            $computedChecksum,
            'flashdrive',
        );

        $batch->update([
            'status' => 'processing',
            'received_at' => now(),
        ]);

        $this->tallyService->processBatch($batch, $ballots);

        $batch->update(['status' => 'complete']);

        $precinct->update(['status' => 'complete']);

        Storage::disk('local')->delete($content);

        Cache::forget('election_tally');

        return [
            'batch_id' => $batch->id,
            'ballot_count' => $batch->ballot_count,
            'status' => $batch->status,
        ];
    }
}
