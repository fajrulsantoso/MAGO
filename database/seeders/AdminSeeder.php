<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_admin' => 1,
                'id_user' => 1, 
                'nip' => '12345678901234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 2,
                'id_user' => 2, 
                'nip' => '12345678901234567891',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 3,
                'id_user' => 3, 
                'nip' => '12345678901234567892',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 4,
                'id_user' => 4, 
                'nip' => '12345678901234567893',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 5,
                'id_user' => 5, 
                'nip' => '12345678901234567894',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 6,
                'id_user' => 6, 
                'nip' => '12345678901234567895',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 7,
                'id_user' => 7, 
                'nip' => '12345678901234567896',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 8,
                'id_user' => 8, 
                'nip' => '12345678901234567897',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 9,
                'id_user' => 9, 
                'nip' => '12345678901234567898',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_admin' => 10,
                'id_user' => 10, 
                'nip' => '12345678901234567899',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('m_admin')->insert($data);

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data admin');
    }
}