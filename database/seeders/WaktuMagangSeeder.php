<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WaktuMagangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_waktu_magang' => 1,
                'waktu_magang' => '3 Bulan',
                'created_at' => now(),
            ],
            [
                'id_waktu_magang' => 2,
                'waktu_magang' => '6 Bulan',
                'created_at' => now(),
            ],
        ];

        DB::table('m_waktu_magang')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data waktu magang');
    }
}
