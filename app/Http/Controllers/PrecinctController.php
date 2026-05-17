<?php

namespace App\Http\Controllers;

use App\Actions\Precinct\CreatePrecinctAction;
use App\Actions\Precinct\DeletePrecinctAction;
use App\Actions\Precinct\RegeneratePrecinctKeysAction;
use App\Actions\Precinct\UpdatePrecinctAction;
use App\Models\Precinct;
use App\Services\PrecinctService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrecinctController extends Controller
{
    public function index(Request $request, PrecinctService $precinctService): Response
    {
        $precincts = $precinctService->list(
            $request->only(['search', 'status', 'region']),
            15,
        );

        return Inertia::render('Precincts/Index', [
            'precincts' => $precincts,
            'filters' => $request->only(['search', 'status', 'region']),
        ]);
    }

    public function store(Request $request, CreatePrecinctAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'precinct_code' => 'required|string|unique:precincts,precinct_code',
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'registered_voters' => 'nullable|integer|min:0',
        ]);

        $result = $action->handle($validated);

        return back()->with('success', 'Precinct created successfully.')->with('precinct_keys', [
            'precinct_code' => $result['precinct']->precinct_code,
            'api_key' => $result['api_key'],
            'aes_key' => $result['aes_key'],
        ]);
    }

    public function update(Precinct $precinct, Request $request, UpdatePrecinctAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'precinct_code' => 'required|string|unique:precincts,precinct_code,'.$precinct->id,
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'registered_voters' => 'nullable|integer|min:0',
            'status' => 'nullable|in:pending,transmitting,partial,complete,error',
        ]);

        $action->handle($precinct, $validated);

        return back()->with('success', 'Precinct updated successfully.');
    }

    public function destroy(Precinct $precinct, DeletePrecinctAction $action): RedirectResponse
    {
        $action->handle($precinct);

        return back()->with('success', 'Precinct deleted successfully.');
    }

    public function regenerateKeys(Precinct $precinct, RegeneratePrecinctKeysAction $action): RedirectResponse
    {
        $keys = $action->handle($precinct);

        return back()->with('success', 'Keys regenerated successfully.')->with('precinct_keys', [
            'precinct_code' => $precinct->precinct_code,
            'api_key' => $keys['api_key'],
            'aes_key' => $keys['aes_key'],
        ]);
    }
}
