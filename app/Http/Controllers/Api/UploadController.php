<?php

namespace App\Http\Controllers\Api;

use App\Actions\Upload\FinalizeUploadAction;
use App\Actions\Upload\InitUploadSessionAction;
use App\Actions\Upload\StoreChunkAction;
use App\Http\Controllers\Controller;
use App\Models\UploadSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function init(Request $request, InitUploadSessionAction $action): JsonResponse
    {
        $precinct = $request->precinct;

        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|string',
            'ballot_count' => 'required|integer|min:1',
            'total_chunks' => 'required|integer|min:1',
            'checksum' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existing = UploadSession::where('batch_id', $request->batch_id)->first();

        if ($existing) {
            return response()->json([
                'message' => 'Batch already being processed',
                'session_id' => $existing->id,
            ], 409);
        }

        $session = $action->handle($precinct, $request->only(['batch_id', 'total_chunks']));

        return response()->json([
            'session_id' => $session->id,
        ]);
    }

    public function chunk(Request $request, StoreChunkAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|uuid',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $session = UploadSession::find($request->session_id);

        if (! $session || $session->status !== 'active') {
            return response()->json(['message' => 'Invalid or expired session'], 404);
        }

        if ($session->expires_at < now()) {
            return response()->json(['message' => 'Session expired'], 410);
        }

        $action->handle($session, $request->chunk_index, $request->data);

        return response()->json([
            'status' => 'ok',
        ]);
    }

    public function finalize(Request $request, FinalizeUploadAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|uuid',
            'checksum' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $session = UploadSession::find($request->session_id);

        if (! $session || $session->status !== 'active') {
            return response()->json(['message' => 'Invalid or expired session'], 404);
        }

        try {
            $batch = $action->handle($session, $request->checksum);

            return response()->json([
                'status' => 'complete',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
