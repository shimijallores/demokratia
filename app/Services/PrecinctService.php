<?php

namespace App\Services;

use App\Models\Precinct;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PrecinctService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Precinct::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('precinct_code', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('region', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['region'])) {
            $query->where('region', $filters['region']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Precinct
    {
        $apiKey = Str::random(64);
        $aesKey = bin2hex(random_bytes(32));

        return Precinct::create([
            'precinct_code' => $data['precinct_code'],
            'name' => $data['name'],
            'region' => $data['region'] ?? null,
            'province' => $data['province'] ?? null,
            'municipality' => $data['municipality'] ?? null,
            'barangay' => $data['barangay'] ?? null,
            'registered_voters' => $data['registered_voters'] ?? null,
            'api_key_hash' => Hash::make($apiKey),
            'aes_key_encrypted' => encrypt($aesKey),
            'status' => 'pending',
        ]);
    }

    public function update(Precinct $precinct, array $data): Precinct
    {
        $precinct->update([
            'precinct_code' => $data['precinct_code'] ?? $precinct->precinct_code,
            'name' => $data['name'] ?? $precinct->name,
            'region' => $data['region'] ?? $precinct->region,
            'province' => $data['province'] ?? $precinct->province,
            'municipality' => $data['municipality'] ?? $precinct->municipality,
            'barangay' => $data['barangay'] ?? $precinct->barangay,
            'registered_voters' => $data['registered_voters'] ?? $precinct->registered_voters,
            'status' => $data['status'] ?? $precinct->status,
        ]);

        return $precinct;
    }

    public function delete(Precinct $precinct): bool
    {
        return $precinct->delete();
    }

    public function regenerateKeys(Precinct $precinct): array
    {
        $apiKey = Str::random(64);
        $aesKey = bin2hex(random_bytes(32));

        $precinct->update([
            'api_key_hash' => Hash::make($apiKey),
            'aes_key_encrypted' => encrypt($aesKey),
        ]);

        return [
            'api_key' => $apiKey,
            'aes_key' => $aesKey,
        ];
    }

    public function findByCode(string $code): ?Precinct
    {
        return Precinct::where('precinct_code', $code)->first();
    }

    public function getStatusCounts(): array
    {
        return Precinct::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    public function getReportingProgress(): array
    {
        $total = Precinct::count();
        $complete = Precinct::where('status', 'complete')->count();
        $transmitting = Precinct::where('status', 'transmitting')->count();
        $partial = Precinct::where('status', 'partial')->count();

        return [
            'total' => $total,
            'complete' => $complete,
            'transmitting' => $transmitting,
            'partial' => $partial,
            'percentage' => $total > 0 ? round(($complete / $total) * 100, 1) : 0,
        ];
    }
}
