<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Membuat array untuk menampung data mahasiswa
        $data = [];
        
        // Prodi D4 Teknik Informatika (ID 1) - 35 mahasiswa
        // Angkatan 2020 - semester 8
        for ($i = 0; $i < 10; $i++) {
            $nim = '2041720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 1,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Angkatan 2021 - semester 6
        for ($i = 10; $i < 20; $i++) {
            $nim = '2141720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 1,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Angkatan 2022 - semester 4
        for ($i = 20; $i < 30; $i++) {
            $nim = '2241720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 1,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Angkatan 2023 - semester 2
        for ($i = 30; $i < 35; $i++) {
            $nim = '2341720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 1,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Prodi D4 Sistem Informasi Bisnis (ID 2) - 35 mahasiswa
        // Angkatan 2020 - semester 8
        for ($i = 35; $i < 45; $i++) {
            $nim = '2042720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 2,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 8,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Angkatan 2021 - semester 6
        for ($i = 45; $i < 55; $i++) {
            $nim = '2142720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 2,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Angkatan 2022 - semester 4
        for ($i = 55; $i < 65; $i++) {
            $nim = '2242720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 2,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Angkatan 2023 - semester 2
        for ($i = 65; $i < 70; $i++) {
            $nim = '2342720' . str_pad($i + 100, 3, '0', STR_PAD_LEFT);
            $data[] = [
                'id_mahasiswa' => $i + 1,
                'id_user' => $i + 11,
                'nim' => $nim,
                'id_prodi' => 2,
                'ipk' => round(mt_rand(275, 400) / 100, 2),
                'semester' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('m_mahasiswa')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data mahasiswa');
    }
}