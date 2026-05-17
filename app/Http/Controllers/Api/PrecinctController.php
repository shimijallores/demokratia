<?php

namespace App\Http\Controllers\Api;

use App\Actions\Precinct\CreatePrecinctAction;
use App\Actions\Precinct\RegeneratePrecinctKeysAction;
use App\Http\Controllers\Controller;
use App\Models\Precinct;
use App\Services\UploadSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrecinctController extends Controller
{
    public function store(Request $request, CreatePrecinctAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'precinct_code' => 'required|string|unique:precincts,precinct_code',
            'name' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'municipality' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'registered_voters' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $precinct = $action->handle($request->all());

        $keys = [
            'api_key' => request()->input('api_key'),
            'aes_key' => request()->input('aes_key'),
        ];

        return response()->json([
            'message' => 'Precinct created successfully',
            'precinct' => $precinct,
            'credentials' => $keys,
        ], 201);
    }

    public function show(Precinct $precinct): JsonResponse
    {
        $precinct->loadCount(['batches', 'votes']);

        return response()->json([
            'precinct' => $precinct,
        ]);
    }

    public function regenerateKeys(Precinct $precinct, RegeneratePrecinctKeysAction $action): JsonResponse
    {
        $keys = $action->handle($precinct);

        return response()->json([
            'message' => 'Keys regenerated successfully',
            'api_key' => $keys['api_key'],
            'aes_key' => $keys['aes_key'],
        ]);
    }

    public function sessions(UploadSessionService $uploadSessionService): JsonResponse
    {
        $sessions = $uploadSessionService->getActiveSessions();

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function abortSession(string $sessionId, UploadSessionService $uploadSessionService): JsonResponse
    {
        $success = $uploadSessionService->abort($sessionId);

        if (! $success) {
            return response()->json(['message' => 'Session not found'], 404);
        }

        return response()->json([
            'message' => 'Session aborted successfully',
        ]);
    }
}
