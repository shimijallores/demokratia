<?php

namespace App\Http\Controllers;

use App\Actions\Candidate\ImportCandidatesAction;
use App\Models\Batch;
use App\Models\Candidate;
use App\Models\Precinct;
use App\Models\Vote;
use App\Services\CandidateService;
use App\Services\FlashDriveImportService;
use App\Services\PrecinctService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ImportController extends Controller
{
    public function index(PrecinctService $precinctService): Response
    {
        return Inertia::render('Import/Index', [
            'precincts' => Precinct::orderBy('precinct_code')->get(['id', 'precinct_code', 'name']),
        ]);
    }

    public function store(Request $request, FlashDriveImportService $importService, PrecinctService $precinctService): RedirectResponse
    {
        $validated = $request->validate([
            'precinct_code' => 'required|string|exists:precincts,precinct_code',
            'file' => 'required|file',
        ]);

        $precinct = $precinctService->findByCode($validated['precinct_code']);

        try {
            $importService->import($precinct, $validated['file']);

            return back()->with('success', 'Flash drive import successful.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }
    }

    public function importCsv(Request $request, CandidateService $candidateService, ImportCandidatesAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file',
        ]);

        $filePath = $request->file('file')->store('imports');
        $content = Storage::disk('local')->get($filePath);

        if (! str_contains($content, 'Ballot ID')) {
            $decrypted = null;
            $precincts = Precinct::all();

            foreach ($precincts as $p) {
                try {
                    $key = decrypt($p->aes_key_encrypted);
                    $binary = base64_decode($content);

                    $iv = substr($binary, 0, 12);
                    $tag = substr($binary, -16);
                    $ciphertext = substr($binary, 12, -16);

                    $dec = openssl_decrypt(
                        $ciphertext,
                        'aes-256-gcm',
                        hex2bin($key),
                        OPENSSL_RAW_DATA,
                        $iv,
                        $tag
                    );

                    if ($dec !== false) {
                        $decrypted = $dec;
                        break;
                    }
                } catch (\Exception $e) {
                    // Try next precinct
                }
            }

            if (! $decrypted) {
                Storage::disk('local')->delete($filePath);

                return back()->withErrors(['file' => 'Failed to decrypt encrypted CSV file. Ensure the correct AES key is configured.']);
            }

            $content = $decrypted;
        }

        $parsed = $candidateService->parseCsv($content);

        Storage::disk('local')->delete($filePath);

        if (empty($parsed)) {
            return back()->withErrors(['file' => 'No valid data found in file']);
        }

        if (isset($parsed[0]['ballot_id'])) {
            return $this->importBallots($parsed);
        }

        $result = $action->handle($parsed);

        if (! empty($result['errors'])) {
            $errorMessages = collect($result['errors'])->flatMap(function ($errors, $index) {
                return collect($errors)->flatMap(fn ($messages, $field) => collect($messages)->map(fn ($msg) => 'Row '.((int) $index + 1)." - {$field}: {$msg}"));
            })->values()->all();

            return back()->with('success', "Imported {$result['imported']}, updated {$result['updated']} candidates with ".count($result['errors']).' errors.')
                ->with('import_errors', $errorMessages);
        }

        return back()->with('success', "Successfully imported {$result['imported']}, updated {$result['updated']} candidates.");
    }

    private function importBallots(array $ballotRows): RedirectResponse
    {
        $grouped = collect($ballotRows)->groupBy('ballot_id');

        $precinct = Precinct::first();

        if (! $precinct) {
            return back()->withErrors(['file' => 'No precinct found. Create a precinct first.']);
        }

        $batch = Batch::create([
            'precinct_id' => $precinct->id,
            'ballot_count' => $grouped->count(),
            'transmission_mode' => 'flashdrive',
            'checksum' => 'csv_import_'.now()->timestamp,
            'status' => 'complete',
        ]);

        $precinct->update(['status' => 'complete']);

        $imported = 0;
        $errors = [];

        foreach ($grouped as $ballotId => $votes) {
            foreach ($votes as $vote) {
                $candidate = Candidate::where('name', $vote['candidate'])
                    ->where('position', $vote['position'])
                    ->first();

                if (! $candidate) {
                    $candidate = Candidate::create([
                        'name' => $vote['candidate'],
                        'position' => $vote['position'],
                        'party' => $vote['party'],
                        'ballot_number' => $vote['ballot_number'] ?? '0',
                    ]);
                }

                Vote::create([
                    'batch_id' => $batch->id,
                    'candidate_id' => $candidate->id,
                    'precinct_id' => $precinct->id,
                    'position' => $vote['position'],
                ]);

                $imported++;
            }
        }

        Cache::forget('election_tally');

        return back()->with('success', "Imported {$imported} votes from {$grouped->count()} ballots.");
    }
}
