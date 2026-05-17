<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Precinct;
use App\Models\UploadSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadSessionService
{
    public function init(Precinct $precinct, array $data): UploadSession
    {
        $session = UploadSession::create([
            'precinct_id' => $precinct->id,
            'batch_id' => $data['batch_id'],
            'total_chunks' => $data['total_chunks'],
            'received_chunks' => [],
            'expires_at' => now()->addHours(4),
            'status' => 'active',
        ]);

        Storage::disk('local')->makeDirectory("chunks/{$session->id}");

        return $session;
    }

    public function storeChunk(UploadSession $session, int $chunkIndex, string $data): void
    {
        $path = "chunks/{$session->id}/{$chunkIndex}.bin";

        Storage::disk('local')->put($path, base64_decode($data));

        $received = $session->received_chunks;

        if (!in_array($chunkIndex, $received)) {
            $received[] = $chunkIndex;
            sort($received);
        }

        $session->update([
            'received_chunks' => $received,
        ]);
    }

    public function finalize(UploadSession $session): string
    {
        $session->update(['status' => 'finalized']);

        $reassembled = '';

        for ($i = 0; $i < $session->total_chunks; $i++) {
            $path = "chunks/{$session->id}/{$i}.bin";

            if (!Storage::disk('local')->exists($path)) {
                throw new \RuntimeException("Missing chunk {$i} for session {$session->id}");
            }

            $reassembled .= Storage::disk('local')->get($path);
        }

        $this->cleanupChunks($session->id);

        return $reassembled;
    }

    public function cleanupChunks(string $sessionId): void
    {
        Storage::disk('local')->deleteDirectory("chunks/{$sessionId}");
    }

    public function findActive(string $sessionId): ?UploadSession
    {
        return UploadSession::where('id', $sessionId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findByBatchId(string $batchId): ?UploadSession
    {
        return UploadSession::where('batch_id', $batchId)->first();
    }

    public function createBatch(Precinct $precinct, string $batchId, int $ballotCount, string $checksum, string $mode): Batch
    {
        return Batch::create([
            'id' => $batchId,
            'precinct_id' => $precinct->id,
            'ballot_count' => $ballotCount,
            'transmission_mode' => $mode,
            'checksum' => $checksum,
            'status' => 'pending',
        ]);
    }

    public function getActiveSessions()
    {
        return UploadSession::where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('precinct')
            ->latest()
            ->get();
    }

    public function abort(string $sessionId): bool
    {
        $session = UploadSession::find($sessionId);

        if (!$session) {
            return false;
        }

        $this->cleanupChunks($session->id);
        $session->update(['status' => 'expired']);

        return true;
    }

    public function cleanupExpired(): int
    {
        $expired = UploadSession::where('status', 'active')
            ->where('expires_at', '<=', now())
            ->get();

        $count = $expired->count();

        foreach ($expired as $session) {
            $this->cleanupChunks($session->id);
            $session->update(['status' => 'expired']);
        }

        return $count;
    }
}
