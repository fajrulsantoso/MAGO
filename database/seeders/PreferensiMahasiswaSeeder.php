<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PreferensiMahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua id mahasiswa yang ada
        $mahasiswaIds = DB::table('m_mahasiswa')->pluck('id_mahasiswa')->toArray();
        
        // Asumsikan ada beberapa daerah magang (dari 1 hingga 514, sesuai dengan jumlah kabupaten/kota di Indonesia)
        // Jika ada data spesifik dari m_daerah_magang, sebaiknya gunakan itu
        $maxDaerah = 514;
        
        // Asumsikan ada 2 jenis waktu magang: 3 Bulan (id: 1) dan 6 Bulan (id: 2)
        $waktuMagangIds = [1, 2];
        
        // Asumsikan ada 2 jenis insentif: Ada (id: 1) dan Tidak Ada (id: 2)
        $insentifIds = [1, 2];
        
        $data = [];
        $id = 1;
        
        foreach ($mahasiswaIds as $mahasiswaId) {
            // Pilih daerah magang secara acak
            $daerahMagang = rand(1, $maxDaerah);
            
            // Pilih waktu magang secara acak
            $waktuMagang = $waktuMagangIds[array_rand($waktuMagangIds)];
            
            // Pilih insentif secara acak - dengan preferensi lebih ke arah "Ada Insentif"
            // 70% kemungkinan memilih "Ada Insentif" (id: 1)
            $insentif = (rand(1, 100) <= 70) ? 1 : 2;
            
            // Buat array ranking 1-5 dan acak urutannya untuk memastikan tidak ada duplikasi
            $availableRankings = [1, 2, 3, 4, 5];
            shuffle($availableRankings);
            
            // Beri bobot pada preferensi berdasarkan karakteristik mahasiswa
            $semester = DB::table('m_mahasiswa')->where('id_mahasiswa', $mahasiswaId)->value('semester');
            
            // Tentukan preferensi berdasarkan logika bisnis yang sudah ada
            // tapi tetap pastikan ranking-nya unik dengan menggunakan array yang sudah diacak
            
            // Preferensi untuk daerah - biasanya semester tinggi lebih fleksibel
            $daerahWeight = min(5, max(1, 3 + floor($semester / 2))); // Angka lebih tinggi = prioritas rendah
            
            // Preferensi waktu magang - biasanya lebih suka waktu pendek
            $waktuWeight = ($waktuMagang == 1) ? 2 : 4;  // Waktu pendek prioritas lebih tinggi
            
            // Preferensi insentif - biasanya sangat diprioritaskan
            $insentifWeight = ($insentif == 1) ? 1 : 4;  // Ada insentif paling diprioritaskan
            
            // Preferensi jenis magang dan bidang (asumsi prioritas menengah)
            $jenisMagangWeight = 3;
            $bidangWeight = 3;
            
            // Buat array dari semua preferensi dan bobot
            $preferences = [
                'daerah' => $daerahWeight,
                'waktu_magang' => $waktuWeight,
                'insentif' => $insentifWeight,
                'jenis_magang' => $jenisMagangWeight,
                'bidang' => $bidangWeight
            ];
            
            // Urutkan preferensi berdasarkan bobot (nilai kecil = prioritas tinggi)
            asort($preferences);
            
            // Assign ranking unik untuk setiap preferensi berdasarkan prioritasnya
            $rankingAssignment = [];
            $rank = 0;
            foreach ($preferences as $prefType => $weight) {
                $rankingAssignment[$prefType] = $availableRankings[$rank];
                $rank++;
            }
            
            $data[] = [
                'id_preferensi' => $id,
                'id_mahasiswa' => $mahasiswaId,
                'id_daerah_magang' => $daerahMagang,
                'ranking_daerah' => $rankingAssignment['daerah'],
                'id_waktu_magang' => $waktuMagang,
                'ranking_waktu_magang' => $rankingAssignment['waktu_magang'],
                'id_insentif' => $insentif,
                'ranking_insentif' => $rankingAssignment['insentif'],
                'ranking_jenis_magang' => $rankingAssignment['jenis_magang'],
                'ranking_bidang' => $rankingAssignment['bidang'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            $id++;
        }
        
        DB::table('r_preferensi_mahasiswa')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data preferensi mahasiswa');
    }
}