<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DospemBidangKeahlianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua dosen pembimbing
        $dospems = DB::table('m_dospem')->get();
        
        // Ambil semua bidang keahlian dari database
        $bidangKeahlian = DB::table('m_bidang_keahlian')->get();
        $bidangCount = $bidangKeahlian->count();
        
        // Pastikan ada bidang keahlian di database
        if ($bidangCount == 0) {
            throw new \Exception('Tabel m_bidang_keahlian kosong. Harap isi terlebih dahulu.');
        }
        
        // Kelompokkan bidang keahlian berdasarkan kategori
        // Bidang keahlian yang berbeda mungkin terkait, jadi kita kelompokkan berdasarkan ID
        // Ini hanya pendekatan simulasi, dalam implementasi nyata clustering mungkin berdasarkan field kategori
        $clusters = [];
        
        // Setiap cluster memiliki 4-6 bidang keahlian
        $clusterSize = min(4, ceil($bidangCount / 5)); // Minimal setiap cluster punya 4 item atau sebagian kecil dari total
        
        // Buat 5 cluster atau kurang jika tidak cukup bidang keahlian
        $numClusters = min(5, ceil($bidangCount / $clusterSize));
        
        // Acak bidang keahlian
        $bidangIDs = $bidangKeahlian->pluck('id_bidang')->toArray();
        shuffle($bidangIDs);
        
        // Buat cluster dengan bidang keahlian yang teracak
        for ($i = 0; $i < $numClusters; $i++) {
            $startIndex = $i * $clusterSize;
            $endIndex = min(($i + 1) * $clusterSize, $bidangCount);
            $clusterLength = $endIndex - $startIndex;
            
            if ($clusterLength > 0) {
                $clusters[$i] = array_slice($bidangIDs, $startIndex, $clusterLength);
            }
        }
        
        $dospemBidangKeahlian = [];
        $timestamp = now();

        foreach ($dospems as $dospem) {
            // Pilih 1-2 cluster utama untuk dosen ini
            $availableClusters = array_keys($clusters);
            $numClustersForDospem = min(count($availableClusters), rand(1, 2));
            $selectedClusterIndices = array_rand($availableClusters, $numClustersForDospem);
            
            if (!is_array($selectedClusterIndices)) {
                $selectedClusterIndices = [$selectedClusterIndices];
            }
            
            $selectedBidangIds = [];
            
            foreach ($selectedClusterIndices as $clusterIdx) {
                $clusterIndex = $availableClusters[$clusterIdx];
                $clusterBidangs = $clusters[$clusterIndex];
                
                // Pilih 2-4 bidang dari cluster ini atau semua jika jumlahnya kurang dari 2
                $numBidangToSelect = min(count($clusterBidangs), rand(2, 4));
                if ($numBidangToSelect < 2 && !empty($clusterBidangs)) {
                    $numBidangToSelect = count($clusterBidangs);
                }
                
                if ($numBidangToSelect > 0) {
                    $selectedIndices = array_rand($clusterBidangs, $numBidangToSelect);
                    if (!is_array($selectedIndices)) {
                        $selectedIndices = [$selectedIndices];
                    }
                    
                    foreach ($selectedIndices as $idx) {
                        $selectedBidangIds[] = $clusterBidangs[$idx];
                    }
                }
            }
            
            // Pastikan tidak ada duplikat
            $selectedBidangIds = array_unique($selectedBidangIds);
            
            // Tambahkan 0-2 bidang random di luar cluster untuk variasi
            $remainingBidangs = array_diff($bidangIDs, $selectedBidangIds);
            if (!empty($remainingBidangs) && rand(0, 100) < 70) {
                $numExtra = min(count($remainingBidangs), rand(1, 2));
                $extraIndices = array_rand($remainingBidangs, $numExtra);
                
                if (!is_array($extraIndices)) {
                    $extraIndices = [$extraIndices];
                }
                
                foreach ($extraIndices as $idx) {
                    $selectedBidangIds[] = $remainingBidangs[$idx];
                }
            }
            
            // Buat record untuk setiap bidang keahlian yang dipilih
            foreach ($selectedBidangIds as $bidangId) {
                $dospemBidangKeahlian[] = [
                    'id_dospem' => $dospem->id_dospem,
                    'id_bidang' => $bidangId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
        }

        // Insert ke database
        DB::table('r_dospem_bidang_keahlian')->insert($dospemBidangKeahlian);

        $this->command->info('Berhasil menyeeder ' . count($dospemBidangKeahlian) . ' data dospem bidang keahlian');
    }
}