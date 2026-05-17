<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FlashDriveImportService;
use App\Services\PrecinctService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    public function flashdrive(Request $request, FlashDriveImportService $importService, PrecinctService $precinctService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'precinct_code' => 'required|string|exists:precincts,precinct_code',
            'file' => 'required|file|mimes:acm,bin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $precinct = $precinctService->findByCode($request->precinct_code);

        try {
            $result = $importService->import($precinct, $request->file('file'));

            return response()->json([
                'message' => 'Import successful',
                'batch_id' => $result['batch_id'],
                'ballot_count' => $result['ballot_count'],
                'status' => $result['status'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
