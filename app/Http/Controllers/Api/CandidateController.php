<?php

namespace App\Http\Controllers\Api;

use App\Actions\Candidate\ImportCandidatesAction;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Services\CandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CandidateController extends Controller
{
    public function __construct(protected CandidateService $candidateService) {}

    public function sync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'candidates' => 'required|array',
            'candidates.*.id' => 'required|integer',
            'candidates.*.name' => 'required|string',
            'candidates.*.position' => 'required|in:president,vice_president,senator,party_list',
            'candidates.*.party' => 'nullable|string',
            'candidates.*.ballot_number' => 'required|string',
            'candidates.*.photo_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $synced = 0;

        foreach ($request->candidates as $data) {
            Candidate::updateOrCreate(
                ['id' => $data['id']],
                [
                    'name' => $data['name'],
                    'position' => $data['position'],
                    'party' => $data['party'] ?? null,
                    'ballot_number' => $data['ballot_number'],
                    'photo_url' => $data['photo_url'] ?? null,
                ]
            );
            $synced++;
        }

        return response()->json([
            'message' => 'Candidates synced successfully',
            'synced' => $synced,
        ]);
    }

    public function import(Request $request, ImportCandidatesAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $content = Storage::disk('local')->get($request->file('file')->store('imports'));

        $candidates = $this->candidateService->parseCsv($content);

        if (empty($candidates)) {
            return response()->json(['message' => 'No valid candidates found in file'], 422);
        }

        $result = $action->handle($candidates);

        return response()->json([
            'message' => 'Import completed',
            'imported' => $result['imported'],
            'errors' => $result['errors'],
        ]);
    }
}
