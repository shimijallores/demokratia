<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CandidateController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Candidate::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('party', 'like', "%{$search}%")
                    ->orWhere('ballot_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('position') && $request->input('position') !== 'all') {
            $query->where('position', $request->input('position'));
        }

        $candidates = $query->orderBy('position')
            ->orderBy('ballot_number')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Candidates/Index', [
            'candidates' => $candidates,
            'filters' => $request->only(['search', 'position']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ballot_number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'position' => 'required|in:president,vice_president,senator,party_list',
            'party' => 'nullable|string|max:255',
            'photo_url' => 'nullable|string|max:255',
        ]);

        Candidate::create($validated);

        return back()->with('success', 'Politician created successfully.');
    }

    public function update(Candidate $candidate, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ballot_number' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'position' => 'required|in:president,vice_president,senator,party_list',
            'party' => 'nullable|string|max:255',
            'photo_url' => 'nullable|string|max:255',
        ]);

        $candidate->update($validated);

        return back()->with('success', 'Politician updated successfully.');
    }

    public function destroy(Candidate $candidate): RedirectResponse
    {
        $candidate->delete();

        return back()->with('success', 'Politician deleted successfully.');
    }

    public function export(): StreamedResponse
    {
        $candidates = Candidate::orderBy('position')->orderBy('ballot_number')->get();

        $response = new StreamedResponse(function () use ($candidates) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['ballot_number', 'name', 'position', 'party', 'photo_url']);

            foreach ($candidates as $candidate) {
                fputcsv($handle, [
                    $candidate->ballot_number,
                    $candidate->name,
                    $candidate->position,
                    $candidate->party,
                    $candidate->photo_url,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="candidates_export.csv"');

        return $response;
    }
}
