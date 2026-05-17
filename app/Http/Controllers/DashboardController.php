<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\UploadSession;
use App\Models\Vote;
use App\Services\PrecinctService;
use App\Services\TallyService;
use App\Services\UploadSessionService;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(PrecinctService $precinctService, TallyService $tallyService, UploadSessionService $uploadSessionService): Response
    {
        $precinctCounts = $precinctService->getStatusCounts();
        $reportingProgress = $precinctService->getReportingProgress();

        $totalBatches = Batch::count();
        $completeBatches = Batch::where('status', 'complete')->count();
        $totalVotes = Vote::count();
        $activeSessions = UploadSession::where('status', 'active')->where('expires_at', '>', now())->count();

        $queueDepth = Cache::get('queue_depth', 0);

        return Inertia::render('Dashboard', [
            'precinctCounts' => $precinctCounts,
            'reportingProgress' => $reportingProgress,
            'totalBatches' => $totalBatches,
            'completeBatches' => $completeBatches,
            'totalVotes' => $totalVotes,
            'activeSessions' => $activeSessions,
            'queueDepth' => $queueDepth,
        ]);
    }
}
