<?php

namespace Database\Seeders;

use App\Models\Precinct;
use Illuminate\Database\Seeder;

class PrecinctSeeder extends Seeder
{
    public function run(): void
    {
        $precincts = [
            [
                'precinct_code' => 'NCR-QC-001',
                'name' => 'Quezon City Precinct 001',
                'region' => 'NCR',
                'province' => 'Metro Manila',
                'municipality' => 'Quezon City',
                'barangay' => 'Barangay Commonwealth',
                'registered_voters' => 1500,
                'status' => 'complete',
            ],
            [
                'precinct_code' => 'NCR-MNL-015',
                'name' => 'Manila Precinct 015',
                'region' => 'NCR',
                'province' => 'Metro Manila',
                'municipality' => 'Manila',
                'barangay' => 'Barangay Ermita',
                'registered_voters' => 1200,
                'status' => 'transmitting',
            ],
            [
                'precinct_code' => 'R3-BUL-042',
                'name' => 'Bulacan Precinct 042',
                'region' => 'Region III',
                'province' => 'Bulacan',
                'municipality' => 'Malolos',
                'barangay' => 'Barangay Dakila',
                'registered_voters' => 900,
                'status' => 'pending',
            ],
            [
                'precinct_code' => 'R4A-LAG-108',
                'name' => 'Laguna Precinct 108',
                'region' => 'Region IV-A',
                'province' => 'Laguna',
                'municipality' => 'Santa Rosa',
                'barangay' => 'Barangay Don Jose',
                'registered_voters' => 1800,
                'status' => 'complete',
            ],
            [
                'precinct_code' => 'R7-CEB-203',
                'name' => 'Cebu Precinct 203',
                'region' => 'Region VII',
                'province' => 'Cebu',
                'municipality' => 'Cebu City',
                'barangay' => 'Barangay Lahug',
                'registered_voters' => 2200,
                'status' => 'partial',
            ],
            [
                'precinct_code' => 'R11-DVO-067',
                'name' => 'Davao Precinct 067',
                'region' => 'Region XI',
                'province' => 'Davao del Sur',
                'municipality' => 'Davao City',
                'barangay' => 'Barangay Matina',
                'registered_voters' => 1600,
                'status' => 'complete',
            ],
            [
                'precinct_code' => 'R12-GEN-091',
                'name' => 'General Santos Precinct 091',
                'region' => 'Region XII',
                'province' => 'South Cotabato',
                'municipality' => 'General Santos City',
                'barangay' => 'Barangay Labangal',
                'registered_voters' => 1100,
                'status' => 'error',
            ],
            [
                'precinct_code' => 'NCR-MKT-033',
                'name' => 'Makati Precinct 033',
                'region' => 'NCR',
                'province' => 'Metro Manila',
                'municipality' => 'Makati',
                'barangay' => 'Barangay Poblacion',
                'registered_voters' => 1400,
                'status' => 'transmitting',
            ],
            [
                'precinct_code' => 'R1-LOC-019',
                'name' => 'Ilocos Norte Precinct 019',
                'region' => 'Region I',
                'province' => 'Ilocos Norte',
                'municipality' => 'Laoag City',
                'barangay' => 'Barangay 1',
                'registered_voters' => 750,
                'status' => 'pending',
            ],
            [
                'precinct_code' => 'R7-BOH-155',
                'name' => 'Bohol Precinct 155',
                'region' => 'Region VII',
                'province' => 'Bohol',
                'municipality' => 'Tagbilaran City',
                'barangay' => 'Barangay Dao',
                'registered_voters' => 850,
                'status' => 'complete',
            ],
        ];

        foreach ($precincts as $data) {
            Precinct::create([
                ...$data,
                'api_key_hash' => bcrypt(bin2hex(random_bytes(32))),
                'aes_key_encrypted' => encrypt(bin2hex(random_bytes(16))),
            ]);
        }
    }
}
