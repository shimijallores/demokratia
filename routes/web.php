<?php

use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\PrecinctController;
use App\Http\Controllers\ResultsController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/', function (): RedirectResponse {
    return redirect(route('login'));
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/precincts', [PrecinctController::class, 'index'])->name('precincts.index');
    Route::post('/precincts', [PrecinctController::class, 'store'])->name('precincts.store');
    Route::put('/precincts/{precinct}', [PrecinctController::class, 'update'])->name('precincts.update');
    Route::delete('/precincts/{precinct}', [PrecinctController::class, 'destroy'])->name('precincts.destroy');
    Route::post('/precincts/{precinct}/regenerate-keys', [PrecinctController::class, 'regenerateKeys'])->name('precincts.regenerate-keys');

    Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
    Route::post('/candidates', [CandidateController::class, 'store'])->name('candidates.store');
    Route::put('/candidates/{candidate}', [CandidateController::class, 'update'])->name('candidates.update');
    Route::delete('/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('candidates.destroy');
    Route::get('/candidates/export', [CandidateController::class, 'export'])->name('candidates.export');

    Route::get('/results', [ResultsController::class, 'index'])->name('results.index');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');
    Route::post('/import/csv', [ImportController::class, 'importCsv'])->name('import.csv');

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

    Route::get('/export', [ExportController::class, 'index'])->name('export.index');
    Route::get('/export/csv', [ExportController::class, 'csv'])->name('export.csv');
    Route::get('/export/json', [ExportController::class, 'json'])->name('export.json');
});
