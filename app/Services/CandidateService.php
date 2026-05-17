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

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('party', 'like', '%'.$filters['search'].'%')
                    ->orWhere('ballot_number', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['position'])) {
            $query->where('position', $filters['position']);
        }

        if (! empty($filters['party'])) {
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
        $updated = 0;
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
                $existing->update([
                    'name' => $candidate['name'],
                    'party' => $candidate['party'] ?? $existing->party,
                    'photo_url' => $candidate['photo_url'] ?? $existing->photo_url,
                ]);
                $updated++;

                continue;
            }

            Candidate::create($candidate);
            $imported++;
        }

        return [
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    public function parseCsv(string $content): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $content)));

        if (empty($lines)) {
            return [];
        }

        $firstLine = array_shift($lines);
        $delimiter = $this->detectDelimiter($firstLine);
        $rawHeaders = $this->parseDelimitedLine($firstLine, $delimiter);

        $headerMap = [];
        foreach ($rawHeaders as $header) {
            $lower = strtolower($header);
            if (in_array($lower, ['ballot_id', 'ballot id'])) {
                $headerMap[$header] = 'ballot_id';
            } elseif (in_array($lower, ['submitted_at', 'submitted at', 'timestamp', 'date'])) {
                $headerMap[$header] = 'submitted_at';
            } elseif (in_array($lower, ['position'])) {
                $headerMap[$header] = 'position';
            } elseif (in_array($lower, ['name', 'candidate'])) {
                $headerMap[$header] = 'name';
            } elseif (in_array($lower, ['party'])) {
                $headerMap[$header] = 'party';
            } elseif (in_array($lower, ['ballot_number', 'ballot number', 'ballotnumber'])) {
                $headerMap[$header] = 'ballot_number';
            } elseif (in_array($lower, ['photo_url', 'photo url', 'photourl', 'photo'])) {
                $headerMap[$header] = 'photo_url';
            }
        }

        $hasBallotId = in_array('ballot_id', $headerMap);
        $rows = [];

        foreach ($lines as $line) {
            $values = $this->parseDelimitedLine($line, $delimiter);

            if (count($values) < count($rawHeaders)) {
                continue;
            }

            $row = array_combine($rawHeaders, $values);
            $mapped = [];

            foreach ($headerMap as $original => $normalized) {
                $mapped[$normalized] = $row[$original] ?? '';
            }

            if ($hasBallotId) {
                if (empty($mapped['ballot_id']) || empty($mapped['name']) || empty($mapped['position'])) {
                    continue;
                }

                $rows[] = [
                    'ballot_id' => $mapped['ballot_id'],
                    'submitted_at' => $mapped['submitted_at'] ?? null,
                    'position' => $this->normalizePosition($mapped['position']),
                    'candidate' => $mapped['name'],
                    'party' => ! empty($mapped['party']) ? $mapped['party'] : null,
                    'ballot_number' => ! empty($mapped['ballot_number']) ? $mapped['ballot_number'] : null,
                ];
            } else {
                if (empty($mapped['ballot_number']) || empty($mapped['name']) || empty($mapped['position'])) {
                    continue;
                }

                $rows[] = [
                    'ballot_number' => $mapped['ballot_number'],
                    'name' => $mapped['name'],
                    'position' => $this->normalizePosition($mapped['position']),
                    'party' => ! empty($mapped['party']) ? $mapped['party'] : null,
                    'photo_url' => ! empty($mapped['photo_url']) ? $mapped['photo_url'] : null,
                ];
            }
        }

        return $rows;
    }

    private function detectDelimiter(string $line): string
    {
        $tabCount = substr_count($line, "\t");
        $commaCount = substr_count($line, ',');

        return $tabCount > $commaCount ? "\t" : ',';
    }

    private function parseDelimitedLine(string $line, string $delimiter): array
    {
        if ($delimiter === ',') {
            return $this->parseCsvLine($line);
        }

        return array_map('trim', explode($delimiter, $line));
    }

    private function parseCsvLine(string $line): array
    {
        $result = [];
        $current = '';
        $inQuotes = false;
        $length = strlen($line);

        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];

            if ($char === '"') {
                if ($inQuotes && isset($line[$i + 1]) && $line[$i + 1] === '"') {
                    $current .= '"';
                    $i++;
                } else {
                    $inQuotes = ! $inQuotes;
                }
            } elseif ($char === ',' && ! $inQuotes) {
                $result[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        $result[] = trim($current);

        return $result;
    }

    public function parseJson(string $content): array
    {
        $data = json_decode($content, true);

        if (! is_array($data)) {
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
