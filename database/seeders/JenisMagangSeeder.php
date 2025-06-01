<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisMagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_jenis_magang' => 'Magang Industri'],
            ['nama_jenis_magang' => 'Magang Akademik'],
            ['nama_jenis_magang' => 'Magang Penelitian'],
            ['nama_jenis_magang' => 'Magang Mandiri'],
            ['nama_jenis_magang' => 'Magang Program Kampus Merdeka'],
        ];

        DB::table('m_jenis_magang')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data jenis magang');
    }
}
