<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            ['id' => 1, 'name' => 'Sara Duterte-Carpio', 'position' => 'president', 'party' => 'Hugpong ng Pagbabago', 'ballot_number' => '001'],
            ['id' => 2, 'name' => 'Bong Go', 'position' => 'president', 'party' => 'PDP-Laban', 'ballot_number' => '002'],
            ['id' => 3, 'name' => 'Risa Hontiveros', 'position' => 'president', 'party' => 'Akbayan', 'ballot_number' => '003'],
            ['id' => 4, 'name' => 'Leni Robredo', 'position' => 'president', 'party' => 'Independent', 'ballot_number' => '004'],
            ['id' => 5, 'name' => 'Erwin Tulfo', 'position' => 'president', 'party' => 'Independent', 'ballot_number' => '005'],

            ['id' => 6, 'name' => 'Bong Go', 'position' => 'vice_president', 'party' => 'PDP-Laban', 'ballot_number' => '006'],
            ['id' => 7, 'name' => 'Kiko Pangilinan', 'position' => 'vice_president', 'party' => 'Liberal Party', 'ballot_number' => '007'],
            ['id' => 8, 'name' => 'Bam Aquino', 'position' => 'vice_president', 'party' => 'Liberal Party', 'ballot_number' => '008'],
            ['id' => 9, 'name' => 'Sherwin Gatchalian', 'position' => 'vice_president', 'party' => 'NPC', 'ballot_number' => '009'],
            ['id' => 10, 'name' => 'Tito Sotto', 'position' => 'vice_president', 'party' => 'NPC', 'ballot_number' => '010'],

            ['id' => 11, 'name' => 'Robin Padilla', 'position' => 'senator', 'party' => 'Independent', 'ballot_number' => '011'],
            ['id' => 12, 'name' => 'Bato dela Rosa', 'position' => 'senator', 'party' => 'PDP-Laban', 'ballot_number' => '012'],
            ['id' => 13, 'name' => 'Imee Marcos', 'position' => 'senator', 'party' => 'NPC', 'ballot_number' => '013'],
            ['id' => 14, 'name' => 'Kiko Pangilinan', 'position' => 'senator', 'party' => 'Liberal Party', 'ballot_number' => '014'],
            ['id' => 15, 'name' => 'Bam Aquino', 'position' => 'senator', 'party' => 'Liberal Party', 'ballot_number' => '015'],
            ['id' => 16, 'name' => 'Sherwin Gatchalian', 'position' => 'senator', 'party' => 'NPC', 'ballot_number' => '016'],
            ['id' => 17, 'name' => 'Risa Hontiveros', 'position' => 'senator', 'party' => 'Akbayan', 'ballot_number' => '017'],
            ['id' => 18, 'name' => 'Panfilo Lacson', 'position' => 'senator', 'party' => 'Independent', 'ballot_number' => '018'],
            ['id' => 19, 'name' => 'Manny Pacquiao', 'position' => 'senator', 'party' => 'PROMDI', 'ballot_number' => '019'],
            ['id' => 20, 'name' => 'Sonny Angara', 'position' => 'senator', 'party' => 'Lakas-CMD', 'ballot_number' => '020'],
            ['id' => 21, 'name' => 'Win Gatchalian', 'position' => 'senator', 'party' => 'NPC', 'ballot_number' => '021'],
            ['id' => 22, 'name' => 'Raffy Tulfo', 'position' => 'senator', 'party' => 'Independent', 'ballot_number' => '022'],
            ['id' => 23, 'name' => 'Francis Tolentino', 'position' => 'senator', 'party' => 'NPC', 'ballot_number' => '023'],
            ['id' => 24, 'name' => 'Jiggy Manicad', 'position' => 'senator', 'party' => 'Independent', 'ballot_number' => '024'],
            ['id' => 25, 'name' => 'Erin Tañada', 'position' => 'senator', 'party' => 'Liberal Party', 'ballot_number' => '025'],
            ['id' => 26, 'name' => 'Leila de Lima', 'position' => 'senator', 'party' => 'Liberal Party', 'ballot_number' => '026'],

            ['id' => 27, 'name' => "Gabriela Women's Party", 'position' => 'party_list', 'party' => 'Gabriela', 'ballot_number' => '027'],
            ['id' => 28, 'name' => 'Bayan Muna', 'position' => 'party_list', 'party' => 'Bayan Muna', 'ballot_number' => '028'],
            ['id' => 29, 'name' => 'ACT Teachers', 'position' => 'party_list', 'party' => 'ACT', 'ballot_number' => '029'],
            ['id' => 30, 'name' => 'Kabataan Party-list', 'position' => 'party_list', 'party' => 'Kabataan', 'ballot_number' => '030'],
            ['id' => 31, 'name' => 'Ako Bicol', 'position' => 'party_list', 'party' => 'Ako Bicol', 'ballot_number' => '031'],
            ['id' => 32, 'name' => 'CIBAC', 'position' => 'party_list', 'party' => 'CIBAC', 'ballot_number' => '032'],
        ];

        foreach ($candidates as $candidate) {
            Candidate::create($candidate);
        }
    }
}
