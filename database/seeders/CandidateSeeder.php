<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $presidentialCandidates = [
            ['name' => 'Juan dela Cruz', 'party' => 'Partido ng Bayan', 'ballot_number' => '001'],
            ['name' => 'Maria Santos', 'party' => 'Lakas ng Masa', 'ballot_number' => '002'],
            ['name' => 'Pedro Reyes', 'party' => 'Alyansa para sa Pagbabago', 'ballot_number' => '003'],
            ['name' => 'Ana Garcia', 'party' => 'Pwersa ng Pag-asa', 'ballot_number' => '004'],
        ];

        foreach ($presidentialCandidates as $candidate) {
            Candidate::create([
                ...$candidate,
                'position' => 'president',
            ]);
        }

        $vpCandidates = [
            ['name' => 'Jose Rizal', 'party' => 'Koalisyon ng Katapatan', 'ballot_number' => '005'],
            ['name' => 'Gabriela Silang', 'party' => 'Unyon ng Mamamayan', 'ballot_number' => '006'],
            ['name' => 'Andres Bonifacio', 'party' => 'Partido Demokratiko', 'ballot_number' => '007'],
            ['name' => 'Melchora Aquino', 'party' => 'Buklod ng Pagkakaisa', 'ballot_number' => '008'],
        ];

        foreach ($vpCandidates as $candidate) {
            Candidate::create([
                ...$candidate,
                'position' => 'vice_president',
            ]);
        }

        $senatorCandidates = [
            ['name' => 'Emilio Aguinaldo', 'party' => 'Partido ng Bayan', 'ballot_number' => '009'],
            ['name' => 'Apolinario Mabini', 'party' => 'Lakas ng Masa', 'ballot_number' => '010'],
            ['name' => 'Antonio Luna', 'party' => 'Alyansa para sa Pagbabago', 'ballot_number' => '011'],
            ['name' => 'Marcelo del Pilar', 'party' => 'Pwersa ng Pag-asa', 'ballot_number' => '012'],
            ['name' => 'Graciano Lopez Jaena', 'party' => 'Koalisyon ng Katapatan', 'ballot_number' => '013'],
            ['name' => 'Francisco Balagtas', 'party' => 'Unyon ng Mamamayan', 'ballot_number' => '014'],
            ['name' => 'Jose Palma', 'party' => 'Partido Demokratiko', 'ballot_number' => '015'],
            ['name' => 'Leonor Rivera', 'party' => 'Buklod ng Pagkakaisa', 'ballot_number' => '016'],
            ['name' => 'Trinidad Tecson', 'party' => 'Partido ng Bayan', 'ballot_number' => '017'],
            ['name' => 'Miguel Malvar', 'party' => 'Lakas ng Masa', 'ballot_number' => '018'],
            ['name' => 'Macario Sakay', 'party' => 'Alyansa para sa Pagbabago', 'ballot_number' => '019'],
            ['name' => 'Artemio Ricarte', 'party' => 'Pwersa ng Pag-asa', 'ballot_number' => '020'],
        ];

        foreach ($senatorCandidates as $candidate) {
            Candidate::create([
                ...$candidate,
                'position' => 'senator',
            ]);
        }

        $partyListCandidates = [
            ['name' => 'Anak ng Bayan Partylist', 'party' => 'Anak ng Bayan', 'ballot_number' => '021'],
            ['name' => 'Manggagawa Coalition', 'party' => 'Manggagawa', 'ballot_number' => '022'],
            ['name' => 'Magsasaka Alliance', 'party' => 'Magsasaka', 'ballot_number' => '023'],
            ['name' => 'Kabataan Representation', 'party' => 'Kabataan', 'ballot_number' => '024'],
            ['name' => 'Indigenous Peoples Voice', 'party' => 'IP Voice', 'ballot_number' => '025'],
            ['name' => 'Senior Citizens First', 'party' => 'Senior First', 'ballot_number' => '026'],
            ['name' => 'Persons with Disability Party', 'party' => 'PWD Party', 'ballot_number' => '027'],
            ['name' => 'Women Empowerment List', 'party' => 'WE List', 'ballot_number' => '028'],
        ];

        foreach ($partyListCandidates as $candidate) {
            Candidate::create([
                ...$candidate,
                'position' => 'party_list',
            ]);
        }
    }
}
