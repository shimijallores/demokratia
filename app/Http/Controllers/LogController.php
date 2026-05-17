<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    public function index(): Response
    {
        $batches = Batch::with('precinct')
            ->latest()
            ->paginate(20);

        return Inertia::render('Logs/Index', [
            'batches' => $batches,
        ]);
    }
}
