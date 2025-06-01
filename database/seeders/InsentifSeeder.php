<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InsentifSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_insentif' => 1,
                'keterangan' => 'Ada',
                'created_at' => now(),
            ],
            [
                'id_insentif' => 2,
                'keterangan' => 'Tidak Ada',
                'created_at' => now(),
            ],
        ];

        DB::table('m_insentif')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data insentif');
    }
}
