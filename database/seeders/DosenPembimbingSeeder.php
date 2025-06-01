<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DosenPembimbingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat array untuk tanggal dengan pola pertumbuhan yang menarik
        $dates = [
            // Awal tahun - pertumbuhan lambat
            Carbon::now()->subMonths(12)->addDays(5),    // Mei 2024
            Carbon::now()->subMonths(11)->addDays(12),   // Juni 2024
            
            // Pertengahan tahun pertama - sedikit peningkatan
            Carbon::now()->subMonths(10)->addDays(3),    // Juli 2024
            Carbon::now()->subMonths(10)->addDays(18),   // Juli 2024
            Carbon::now()->subMonths(9)->addDays(7),     // Agustus 2024
            
            // Akhir tahun - stabil
            Carbon::now()->subMonths(8)->addDays(10),    // September 2024
            Carbon::now()->subMonths(7)->addDays(22),    // Oktober 2024
            
            // Awal tahun baru - pertumbuhan cepat
            Carbon::now()->subMonths(6)->addDays(5),     // November 2024
            Carbon::now()->subMonths(6)->addDays(15),    // November 2024
            Carbon::now()->subMonths(5)->addDays(8),     // Desember 2024
            Carbon::now()->subMonths(5)->addDays(20),    // Desember 2024
            
            // Pertumbuhan paling cepat - awal semester baru
            Carbon::now()->subMonths(4)->addDays(7),     // Januari 2025
            Carbon::now()->subMonths(4)->addDays(17),    // Januari 2025
            Carbon::now()->subMonths(4)->addDays(27),    // Januari 2025
            Carbon::now()->subMonths(3)->addDays(10),    // Februari 2025
            Carbon::now()->subMonths(3)->addDays(22),    // Februari 2025
            
            // Stabilisasi kembali
            Carbon::now()->subMonths(2)->addDays(5),     // Maret 2025
            Carbon::now()->subMonths(1)->addDays(12),    // April 2025
            
            // Bulan ini - terbaru
            Carbon::now()->subDays(15),                  // Mei 2025
            Carbon::now()->subDays(5),                   // Mei 2025
        ];

        $data = [];
        
        // Memastikan jumlah dosen sesuai dengan jumlah tanggal
        for ($i = 0; $i < min(20, count($dates)); $i++) {
            $data[] = [
                'id_dospem' => $i + 1,
                'id_user' => $i + 81,
                'nip' => $this->generateNIP($i),
                'created_at' => $dates[$i],
                'updated_at' => $dates[$i],
            ];
        }

        DB::table('m_dospem')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data dosen pembimbing');
    }
    
    /**
     * Generate NIP unik
     */
    private function generateNIP($index)
    {
        // Gunakan data NIP yang sudah ada
        $nipData = [
            '198804162011031001',
            '197506232005011002',
            '196809152000032001',
            '197203102003121003',
            '198012182006042002',
            '197605082002121004',
            '198302142007012003',
            '196509271995031005',
            '198107092008012004',
            '197108232001121006',
            '198504122010042005',
            '197309112004121007',
            '196902152000032006',
            '198212252009121008',
            '197711172003122007',
            '196803211994031009',
            '198601082011012008',
            '197904232005011010',
            '197010102000032009',
            '198508182012121011',
        ];
        
        return isset($nipData[$index]) ? $nipData[$index] : '19' . rand(60, 99) . rand(01, 12) . rand(10, 28) . rand(1990, 2015) . rand(01, 12) . rand(1000, 9999);
    }
}