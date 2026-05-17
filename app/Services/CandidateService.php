<?php

namespace App\Services;

use App\Models\Candidate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class CandidateService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Candidate::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('party', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('ballot_number', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (!empty($filters['party'])) {
            $query->where('party', $filters['party']);
        }

        return $query->orderBy('position')->orderBy('name')->paginate($perPage);
    }

    public function getByPosition(): Collection
    {
        return Candidate::orderBy('position')->orderBy('name')->get()->groupBy('position');
    }

    public function create(array $data): Candidate
    {
        return Candidate::create($data);
    }

    public function update(Candidate $candidate, array $data): Candidate
    {
        $candidate->update($data);

        return $candidate;
    }

    public function delete(Candidate $candidate): bool
    {
        return $candidate->delete();
    }

    public function import(array $candidates): array
    {
        $imported = 0;
        $errors = [];

        foreach ($candidates as $index => $candidate) {
            $validator = Validator::make($candidate, [
                'name' => 'required|string|max:255',
                'position' => 'required|in:president,vice_president,senator,party_list',
                'party' => 'nullable|string|max:255',
                'ballot_number' => 'required|string|max:50',
                'photo_url' => 'nullable|url|max:255',
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors()->toArray();

                continue;
            }

            $existing = Candidate::where('position', $candidate['position'])
                ->where('ballot_number', $candidate['ballot_number'])
                ->first();

            if ($existing) {
                $errors[$index] = ['ballot_number' => ['Duplicate ballot number for this position']];

                continue;
            }

            Candidate::create($candidate);
            $imported++;
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    public function parseCsv(string $content): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        $headers = array_map('trim', explode(',', array_shift($lines)));

        $candidates = [];

        foreach ($lines as $line) {
            $values = array_map('trim', explode(',', $line));

            if (count($values) < 3) {
                continue;
            }

            $row = array_combine($headers, $values);

            $candidates[] = [
                'ballot_number' => $row['ballot_number'] ?? '',
                'name' => $row['name'] ?? '',
                'position' => $this->normalizePosition($row['position'] ?? ''),
                'party' => $row['party'] ?? null,
                'photo_url' => !empty($row['photo_url']) ? $row['photo_url'] : null,
            ];
        }

        return $candidates;
    }

    public function parseJson(string $content): array
    {
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return [];
        }

        return array_map(function ($candidate) {
            return [
                'ballot_number' => $candidate['ballot_number'] ?? '',
                'name' => $candidate['name'] ?? '',
                'position' => $this->normalizePosition($candidate['position'] ?? ''),
                'party' => $candidate['party'] ?? null,
                'photo_url' => $candidate['photo_url'] ?? null,
            ];
        }, $data);
    }

    private function normalizePosition(string $position): string
    {
        $position = strtolower(trim($position));

        $mapping = [
            'president' => 'president',
            'vice president' => 'vice_president',
            'vicepresident' => 'vice_president',
            'vp' => 'vice_president',
            'senator' => 'senator',
            'senators' => 'senator',
            'party-list' => 'party_list',
            'partylist' => 'party_list',
            'party list' => 'party_list',
            'party_list' => 'party_list',
        ];

        return $mapping[$position] ?? $position;
    }
}
