<?php

namespace App\Console\Commands;

use App\Services\UploadSessionService;
use Illuminate\Console\Command;

class CleanupExpiredSessions extends Command
{
    protected $signature = 'app:cleanup-expired-sessions';

    protected $description = 'Clean up expired upload sessions and their chunk files';

    public function handle(UploadSessionService $uploadSessionService): int
    {
        $cleaned = $uploadSessionService->cleanupExpired();

        $this->info("Cleaned up {$cleaned} expired upload session(s).");

        return Command::SUCCESS;
    }
}
