<?php

namespace Database\Seeders;

use App\Models\Auth\RoleModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'id_role' => 1,
                'name' => 'admin', // spatie default name
                'guard_name' => 'web', // spatie default guard
                'nama_role' => 'Admin',
                'kode_role' => 'ADM',
            ],
            [
                'id_role' => 2,
                'name' => 'mahasiswa', // spatie default name
                'guard_name' => 'web', // spatie default guard
                'nama_role' => 'Mahasiswa',
                'kode_role' => 'MHS',
            ],
            [
                'id_role' => 3,
                'name' => 'dosen_pembimbing', // spatie default name
                'guard_name' => 'web', // spatie default guard
                'nama_role' => 'Dosen Pembimbing',
                'kode_role' => 'DSP',
            ],
        ];

        // DB::table('m_role')->insert($data);
        foreach ($data as $role) {
            RoleModel::updateOrCreate(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                $role
            );
        }

        $this->command->info('Berhasil menyeeder ' . count($data) . ' data role');
    }
}
