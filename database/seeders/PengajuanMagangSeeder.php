<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengajuanMagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gunakan current date sebagai referensi (2025-05-28)
        $currentDate = Carbon::parse('2025-05-28');
        
        // Ambil semua data lowongan magang untuk referensi
        $lowongan = DB::table('t_lowongan_magang')->get();
        
        // Ambil semua data mahasiswa untuk referensi
        $mahasiswa = DB::table('m_mahasiswa')->get();
        
        // Track pengajuan yang sudah dibuat untuk menghindari duplikat mahasiswa-lowongan
        $existingApplications = [];
        
        $data = [];
        $counter = 1;
        
        // Buat 150 data pengajuan magang
        $validRecords = 0;
        while ($validRecords < 150) {
            // Pilih mahasiswa secara acak
            $selectedMahasiswa = $mahasiswa->random();
            $idMahasiswa = $selectedMahasiswa->id_mahasiswa;
            
            // Pilih lowongan secara acak
            $selectedLowongan = $lowongan->random();
            $idLowongan = $selectedLowongan->id_lowongan;
            
            // Cek apakah kombinasi mahasiswa-lowongan sudah ada
            $applicationKey = $idMahasiswa . '-' . $idLowongan;
            if (in_array($applicationKey, $existingApplications)) {
                // Skip iterasi ini dan coba lagi
                continue;
            }
            
            // Catat kombinasi mahasiswa-lowongan
            $existingApplications[] = $applicationKey;
            
            // Parse tanggal lowongan
            $tanggalPosting = Carbon::parse($selectedLowongan->tanggal_posting);
            $batasAkhir = Carbon::parse($selectedLowongan->batas_akhir_lamaran);
            
            // Buat tanggal pengajuan antara tanggal posting dan batas akhir
            $daysAfterPosting = rand(1, max(1, $batasAkhir->diffInDays($tanggalPosting)));
            $tanggalPengajuan = $tanggalPosting->copy()->addDays($daysAfterPosting);
            
            // Pastikan tanggal pengajuan tidak melewati tanggal saat ini atau batas akhir
            if ($tanggalPengajuan->greaterThan($currentDate) || $tanggalPengajuan->greaterThan($batasAkhir)) {
                if ($currentDate->lessThan($batasAkhir)) {
                    $tanggalPengajuan = $currentDate->copy()->subDays(rand(0, min(30, $currentDate->diffInDays($tanggalPosting))));
                } else {
                    $tanggalPengajuan = $batasAkhir->copy()->subDays(rand(1, max(1, $batasAkhir->diffInDays($tanggalPosting))));
                }
            }
            
            // Tentukan status pengajuan
            // Untuk lowongan aktif, 70% diajukan, 20% diterima, 10% ditolak
            // Untuk lowongan selesai, 10% diajukan, 60% diterima, 30% ditolak
            $statusProbability = $selectedLowongan->status === 'Aktif' ? 
                ['Diajukan' => 70, 'Diterima' => 20, 'Ditolak' => 10] :
                ['Diajukan' => 10, 'Diterima' => 60, 'Ditolak' => 30];
            
            $randomValue = rand(1, 100);
            $cumulativeProbability = 0;
            $status = 'Diajukan'; // Default
            
            foreach ($statusProbability as $key => $probability) {
                $cumulativeProbability += $probability;
                if ($randomValue <= $cumulativeProbability) {
                    $status = $key;
                    break;
                }
            }
            
            // Tentukan tanggal diterima jika status bukan 'Diajukan'
            $tanggalDiterima = null;
            if ($status !== 'Diajukan') {
                // Tanggal diterima adalah 3-14 hari setelah pengajuan
                $tanggalDiterima = $tanggalPengajuan->copy()->addDays(rand(3, 14));
                
                // Pastikan tanggal diterima tidak melewati tanggal saat ini
                if ($tanggalDiterima->greaterThan($currentDate)) {
                    $tanggalDiterima = $currentDate->copy()->subDays(rand(0, 5));
                    
                    // Jika tanggal diterima jadi lebih awal dari tanggal pengajuan, pakai tanggal pengajuan + 1-3 hari
                    if ($tanggalDiterima->lessThan($tanggalPengajuan)) {
                        $tanggalDiterima = $tanggalPengajuan->copy()->addDays(rand(1, 3));
                    }
                }
            }
            
            $data[] = [
                'id_pengajuan' => $counter,
                'id_mahasiswa' => $idMahasiswa,
                'id_lowongan' => $idLowongan,
                'tanggal_pengajuan' => $tanggalPengajuan->format('Y-m-d'),
                'status' => $status,
                'tanggal_diterima' => $tanggalDiterima ? $tanggalDiterima->format('Y-m-d') : null,
                'created_at' => $tanggalPengajuan->format('Y-m-d H:i:s'),
                'updated_at' => $tanggalDiterima ? $tanggalDiterima->format('Y-m-d H:i:s') : $tanggalPengajuan->format('Y-m-d H:i:s'),
            ];
            
            $counter++;
            
            // Jika sudah mencapai 150 data valid, hentikan loop
            if ($counter > 150) {
                break;
            }
        }
        
        // Insert semua data ke database
        DB::table('t_pengajuan_magang')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data pengajuan magang');
    }
}