<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreferensiBidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua data preferensi mahasiswa
        $preferensi = DB::table('r_preferensi_mahasiswa')->get();
        
        // Ambil semua bidang keahlian
        $bidangKeahlian = DB::table('m_bidang_keahlian')->get();
        $totalBidang = $bidangKeahlian->count();
        
        // Pastikan ada bidang keahlian di database
        if ($totalBidang == 0) {
            throw new \Exception('Tabel m_bidang_keahlian kosong. Harap isi terlebih dahulu.');
        }
        
        $data = [];
        $timestamp = now();
        
        foreach ($preferensi as $pref) {
            // Untuk tiap mahasiswa, tentukan berapa bidang yang akan dipilih
            // Mahasiswa biasanya memilih beberapa bidang keahlian, tidak semua
            // Minimal pilih 3 bidang, maksimal pilih setengah dari total bidang
            $maxBidang = min($totalBidang, max(5, (int)($totalBidang / 2)));
            $bidangToSelect = rand(3, $maxBidang);
            
            // Acak urutan bidang keahlian untuk dipilih
            $bidangIDs = $bidangKeahlian->pluck('id_bidang')->toArray();
            shuffle($bidangIDs);
            $selectedBidang = array_slice($bidangIDs, 0, $bidangToSelect);
            
            // Simpan semua bidang yang dipilih
            foreach ($selectedBidang as $bidangId) {
                $data[] = [
                    'id_preferensi' => $pref->id_preferensi,
                    'id_bidang' => $bidangId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
        }
        
        // Insert ke database
        DB::table('r_preferensi_bidang')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data preferensi bidang');
    }
}
