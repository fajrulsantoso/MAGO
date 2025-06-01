<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_prodi' => 1,
                'nama_prodi' => 'D4 Teknik Informatika',
                'kode_prodi' => 'D4-TI',
                'created_at' => now(),
            ],
            [
                'id_prodi' => 2,
                'nama_prodi' => 'D4 Sistem Informasi Bisnis',
                'kode_prodi' => 'D4-SIB',
                'created_at' => now(),
            ],
        ];

        DB::table('m_prodi')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data prodi');
    }
}
