<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreferensiJenisMagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua data preferensi mahasiswa
        $preferensi = DB::table('r_preferensi_mahasiswa')->get();
        
        // Ambil semua jenis magang
        $jenisMagang = DB::table('m_jenis_magang')->get();
        $totalJenisMagang = $jenisMagang->count();
        
        // Pastikan ada jenis magang di database
        if ($totalJenisMagang == 0) {
            throw new \Exception('Tabel m_jenis_magang kosong. Harap isi terlebih dahulu.');
        }
        
        $data = [];
        $timestamp = now();
        
        foreach ($preferensi as $pref) {
            // Untuk tiap mahasiswa, tentukan berapa jenis magang yang akan dipilih
            // Sebagian besar mahasiswa akan memilih semua jenis magang
            // Beberapa mahasiswa hanya akan memilih beberapa jenis magang saja
            $jenisMagangToSelect = ($totalJenisMagang > 3 && rand(1, 10) <= 2) ? 
                rand(3, $totalJenisMagang - 1) : $totalJenisMagang;
            
            // Acak urutan jenis magang untuk dipilih
            $jenisMagangIDs = $jenisMagang->pluck('id_jenis_magang')->toArray();
            shuffle($jenisMagangIDs);
            $selectedJenisMagang = array_slice($jenisMagangIDs, 0, $jenisMagangToSelect);
            
            // Simpan semua jenis magang yang dipilih tanpa ranking
            foreach ($selectedJenisMagang as $jenisMagangId) {
                $data[] = [
                    'id_preferensi' => $pref->id_preferensi,
                    'id_jenis_magang' => $jenisMagangId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
        }
        
        // Insert ke database
        DB::table('r_preferensi_jenis_magang')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data preferensi jenis magang');
    }
}