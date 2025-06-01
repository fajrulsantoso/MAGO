<?php

namespace Database\Seeders;

use App\Models\Reference\ProvinsiModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/dataset/provinsi_dataset.csv');
        $csv = fopen($csvFile, 'r');

        // Skip header
        fgetcsv($csv);

        $totalRows = count(file($csvFile)) - 1; // Total rows excluding header

        while (($data = fgetcsv($csv)) !== FALSE) {
            ProvinsiModel::updateOrCreate(
                ['id_provinsi' => $data[0]],
                ['nama_provinsi' => $data[1]]
            );
        }

        fclose($csv);

        $this->command->info('Berhasil menyeeder ' . $totalRows . ' data admin');
    }
}
