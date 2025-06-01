<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangKeahlianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id_bidang' => 1, 'nama_bidang_keahlian' => 'Web Development'],
            ['id_bidang' => 2, 'nama_bidang_keahlian' => 'Mobile Development'],
            ['id_bidang' => 3, 'nama_bidang_keahlian' => 'UI/UX Design'],
            ['id_bidang' => 4, 'nama_bidang_keahlian' => 'Network Engineering'],
            ['id_bidang' => 5, 'nama_bidang_keahlian' => 'Cloud Computing'],
            ['id_bidang' => 6, 'nama_bidang_keahlian' => 'Cyber Security'],
            ['id_bidang' => 7, 'nama_bidang_keahlian' => 'Database Administration'],
            ['id_bidang' => 8, 'nama_bidang_keahlian' => 'Game Development'],
            ['id_bidang' => 9, 'nama_bidang_keahlian' => 'Embedded Systems'],
            ['id_bidang' => 10, 'nama_bidang_keahlian' => 'Machine Learning'],
            ['id_bidang' => 11, 'nama_bidang_keahlian' => 'Artificial Intelligence'],
            ['id_bidang' => 12, 'nama_bidang_keahlian' => 'Data Analysis'],
            ['id_bidang' => 13, 'nama_bidang_keahlian' => 'DevOps'],
            ['id_bidang' => 14, 'nama_bidang_keahlian' => 'Software Testing'],
            ['id_bidang' => 15, 'nama_bidang_keahlian' => 'Business Intelligence'],
            ['id_bidang' => 16, 'nama_bidang_keahlian' => 'IT Support'],
            ['id_bidang' => 17, 'nama_bidang_keahlian' => 'Blockchain Development'],
            ['id_bidang' => 18, 'nama_bidang_keahlian' => 'Digital Forensics'],
            ['id_bidang' => 19, 'nama_bidang_keahlian' => 'AR/VR Development'],
            ['id_bidang' => 20, 'nama_bidang_keahlian' => 'Robotic Process Automation'],
        ];

        DB::table('m_bidang_keahlian')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data bidang keahlian');
    }
}
