<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LowonganBidangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua lowongan magang
        $lowongan = DB::table('t_lowongan_magang')->get();
        
        // Ambil semua bidang keahlian
        $bidangKeahlian = DB::table('m_bidang_keahlian')->get();
        $bidangCount = $bidangKeahlian->count();
        
        // Pastikan ada bidang keahlian di database
        if ($bidangCount == 0) {
            throw new \Exception('Tabel m_bidang_keahlian kosong. Harap isi terlebih dahulu.');
        }
        
        // Mapping bidang keahlian berdasarkan kata kunci yang mungkin muncul di judul lowongan
        // Ini hanya contoh mapping, bisa disesuaikan dengan data aktual di database
        $keywordToBidang = [
            // Web Development
            'web' => [],
            'front' => [],
            'backend' => [],
            'full stack' => [],
            'php' => [],
            'javascript' => [],
            'html' => [],
            'css' => [],
            
            // Mobile
            'mobile' => [],
            'android' => [],
            'ios' => [],
            'flutter' => [],
            'react native' => [],
            
            // Data Science
            'data' => [],
            'analyst' => [],
            'analytics' => [],
            'science' => [],
            'big data' => [],
            'machine learning' => [],
            'ml' => [],
            'ai' => [],
            'artificial' => [],
            
            // UI/UX
            'ui' => [],
            'ux' => [],
            'design' => [],
            'interface' => [],
            'user experience' => [],
            
            // Network & Security
            'network' => [],
            'security' => [],
            'cyber' => [],
            'system' => [],
            'administrator' => [],
            'cloud' => [],
            'devops' => [],
            
            // Business & Management
            'business' => [],
            'management' => [],
            'project' => [],
            'product' => [],
            'marketing' => [],
            'digital marketing' => [],
            
            // Others
            'content' => [],
            'writer' => [],
            'social media' => [],
            'graphic' => [],
            'video' => [],
            'finance' => [],
            'accounting' => [],
            'hr' => [],
            'human resource' => [],
        ];
        
        // Kelompokkan bidang keahlian berdasarkan kategori
        // Ini adalah kategori berdasarkan topik umum di bidang IT dan bisnis
        $categories = [
            'web_development' => [],
            'mobile_development' => [],
            'data_science' => [],
            'ui_ux' => [],
            'network_security' => [],
            'business_management' => [],
            'content_media' => [],
            'other' => []
        ];
        
        // Distribusikan bidang keahlian ke kategori secara merata
        // Di implementasi nyata, ini bisa berdasarkan field kategori di tabel m_bidang_keahlian
        $bidangArray = $bidangKeahlian->toArray();
        shuffle($bidangArray);
        
        $categoryKeys = array_keys($categories);
        $categoryCount = count($categoryKeys);
        
        foreach ($bidangArray as $index => $bidang) {
            $categoryIndex = $index % $categoryCount;
            $categories[$categoryKeys[$categoryIndex]][] = $bidang->id_bidang;
        }
        
        // Untuk setiap keyword, pilih bidang keahlian dari kategori yang relevan
        foreach ($keywordToBidang as $keyword => &$bidangIds) {
            if (Str::contains($keyword, ['web', 'front', 'backend', 'full stack', 'php', 'javascript', 'html', 'css'])) {
                $bidangIds = $categories['web_development'];
            } elseif (Str::contains($keyword, ['mobile', 'android', 'ios', 'flutter', 'react native'])) {
                $bidangIds = $categories['mobile_development'];
            } elseif (Str::contains($keyword, ['data', 'analyst', 'analytics', 'science', 'big data', 'machine learning', 'ml', 'ai', 'artificial'])) {
                $bidangIds = $categories['data_science'];
            } elseif (Str::contains($keyword, ['ui', 'ux', 'design', 'interface', 'user experience'])) {
                $bidangIds = $categories['ui_ux'];
            } elseif (Str::contains($keyword, ['network', 'security', 'cyber', 'system', 'administrator', 'cloud', 'devops'])) {
                $bidangIds = $categories['network_security'];
            } elseif (Str::contains($keyword, ['business', 'management', 'project', 'product', 'marketing', 'digital marketing'])) {
                $bidangIds = $categories['business_management'];
            } elseif (Str::contains($keyword, ['content', 'writer', 'social media', 'graphic', 'video'])) {
                $bidangIds = $categories['content_media'];
            } else {
                $bidangIds = $categories['other'];
            }
        }
        
        $lowonganBidang = [];
        $timestamp = now();
        
        foreach ($lowongan as $job) {
            // Tentukan berapa banyak bidang keahlian untuk lowongan ini (2-5)
            $numBidang = rand(2, 5);
            $selectedBidangIds = [];
            
            // Cari kata kunci dalam judul lowongan
            $jobTitle = strtolower($job->judul_lowongan);
            $matchedKeywords = [];
            
            foreach ($keywordToBidang as $keyword => $bidangIds) {
                if (Str::contains($jobTitle, $keyword)) {
                    $matchedKeywords[] = $keyword;
                }
            }
            
            // Jika ada kata kunci yang cocok, pilih bidang dari kategori yang sesuai
            if (!empty($matchedKeywords)) {
                // Pilih kata kunci secara acak jika ada beberapa yang cocok
                $selectedKeyword = $matchedKeywords[array_rand($matchedKeywords)];
                $relevantBidangIds = $keywordToBidang[$selectedKeyword];
                
                if (!empty($relevantBidangIds)) {
                    // Pilih bidang keahlian yang relevan
                    $numRelevantToSelect = min(count($relevantBidangIds), ceil($numBidang * 0.7));
                    
                    if ($numRelevantToSelect > 0) {
                        $selectedIndices = array_rand($relevantBidangIds, $numRelevantToSelect);
                        
                        if (!is_array($selectedIndices)) {
                            $selectedIndices = [$selectedIndices];
                        }
                        
                        foreach ($selectedIndices as $idx) {
                            $selectedBidangIds[] = $relevantBidangIds[$idx];
                        }
                    }
                }
            }
            
            // Tambahkan bidang keahlian acak untuk melengkapi jumlah yang dibutuhkan
            $remainingToSelect = $numBidang - count($selectedBidangIds);
            
            if ($remainingToSelect > 0) {
                $allBidangIds = $bidangKeahlian->pluck('id_bidang')->toArray();
                $remainingBidangIds = array_diff($allBidangIds, $selectedBidangIds);
                
                if (!empty($remainingBidangIds)) {
                    $numRemainingToSelect = min(count($remainingBidangIds), $remainingToSelect);
                    $additionalIndices = array_rand(array_flip($remainingBidangIds), $numRemainingToSelect);
                    
                    if (!is_array($additionalIndices)) {
                        $additionalIndices = [$additionalIndices];
                    }
                    
                    $selectedBidangIds = array_merge($selectedBidangIds, $additionalIndices);
                }
            }
            
            // Buat record untuk setiap bidang keahlian yang dipilih
            foreach ($selectedBidangIds as $bidangId) {
                $lowonganBidang[] = [
                    'id_lowongan' => $job->id_lowongan,
                    'id_bidang' => $bidangId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp
                ];
            }
        }
        
        // Insert ke database
        DB::table('r_lowongan_bidang')->insert($lowonganBidang);

        $this->command->info('Berhasil menyeeder ' . count($lowonganBidang) . ' data lowongan bidang');
    }
}