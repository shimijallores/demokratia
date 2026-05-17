<?php

use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\PrecinctController;
use App\Http\Controllers\Api\ResultsController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware(['precinct.api'])->group(function () {
        Route::post('/upload/init', [UploadController::class, 'init']);
        Route::post('/upload/chunk', [UploadController::class, 'chunk'])->middleware('throttle:120,1');
        Route::post('/upload/finalize', [UploadController::class, 'finalize']);
        Route::post('/candidates/sync', [CandidateController::class, 'sync']);
        Route::post('/candidates/import', [CandidateController::class, 'import']);
    });

    Route::post('/import/flashdrive', [ImportController::class, 'flashdrive'])->middleware('auth');

    Route::get('/results/live', [ResultsController::class, 'live']);
    Route::get('/results/positions/{position}', [ResultsController::class, 'byPosition']);
    Route::get('/precincts/status', [ResultsController::class, 'precinctStatus']);
    Route::get('/precincts/{precinct}', [ResultsController::class, 'precinctDetail'])->middleware('auth');

    Route::middleware(['auth'])->group(function () {
        Route::post('/precincts', [PrecinctController::class, 'store']);
        Route::get('/precincts/{precinct}', [PrecinctController::class, 'show']);
        Route::put('/precincts/{precinct}/keys', [PrecinctController::class, 'regenerateKeys']);
        Route::get('/admin/sessions', [PrecinctController::class, 'sessions']);
        Route::delete('/admin/sessions/{sessionId}', [PrecinctController::class, 'abortSession']);
    });
});
