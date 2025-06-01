<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [];
        $id = 1;
        
        // Membuat data periode dari 1975/1976 hingga 2024/2025
        for ($tahun = 1975; $tahun <= 2024; $tahun++) {
            // Semester Ganjil
            $data[] = [
                'id_periode' => $id++,
                'nama_periode' => $tahun . '/' . ($tahun + 1) . ' Ganjil',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Semester Genap
            $data[] = [
                'id_periode' => $id++,
                'nama_periode' => $tahun . '/' . ($tahun + 1) . ' Genap',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Menambahkan beberapa periode pendek/antara untuk variasi
        $pendek = [
            ['id_periode' => $id++, 'nama_periode' => '2020/2021 Antara', 'created_at' => now(), 'updated_at' => now()],
            ['id_periode' => $id++, 'nama_periode' => '2021/2022 Antara', 'created_at' => now(), 'updated_at' => now()],
            ['id_periode' => $id++, 'nama_periode' => '2022/2023 Antara', 'created_at' => now(), 'updated_at' => now()],
            ['id_periode' => $id++, 'nama_periode' => '2023/2024 Antara', 'created_at' => now(), 'updated_at' => now()],
        ];
        
        $data = array_merge($data, $pendek);
        
        // Menambahkan periode masa depan
        for ($tahun = 2025; $tahun <= 2034; $tahun++) {
            // Semester Ganjil
            $data[] = [
                'id_periode' => $id++,
                'nama_periode' => $tahun . '/' . ($tahun + 1) . ' Ganjil',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Semester Genap
            $data[] = [
                'id_periode' => $id++,
                'nama_periode' => $tahun . '/' . ($tahun + 1) . ' Genap',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        DB::table('m_periode')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data periode');
    }
}