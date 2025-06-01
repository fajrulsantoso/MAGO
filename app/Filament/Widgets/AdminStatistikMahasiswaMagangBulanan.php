<?php

namespace App\Filament\Widgets;

use App\Models\Reference\PenempatanMagangModel;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Symfony\Component\VarDumper\VarDumper;

class AdminStatistikMahasiswaMagangBulanan extends ChartWidget
{
    protected static ?string $heading = 'Grafik Mahasiswa Magang per Bulan';
    protected static ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $startDate = Carbon::now()->subMonths(11); // 11 bulan sebelumnya + bulan ini = 12 bulan
        $endDate = Carbon::now();

        $monthlyData = [];
        $labels = [];

        // Buat array dengan semua bulan dari rentang waktu yang dipilih
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $monthlyData[$monthKey] = 0; // Inisialisasi semua data dengan 0

            $labels[$monthKey] = $currentDate->translatedFormat('M Y');
            $currentDate->addMonth();
        }

        // Query data jumlah mahasiswa per bulan
        $results = PenempatanMagangModel::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan, COUNT(DISTINCT id_mahasiswa) as jumlah')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy('bulan')
            ->pluck('jumlah', 'bulan')
            ->toArray();

        // Masukkan data ke array sesuai bulan
        foreach ($results as $bulan => $jumlah) {
            if (array_key_exists($bulan, $monthlyData)) {
                $monthlyData[$bulan] = $jumlah;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Mahasiswa Magang',
                    'data' => array_values($monthlyData),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#60a5fa80',
                    'fill' => true,
                ],
            ],
            'labels' => array_values($labels),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'min' => 0,
                ],
            ],
            // Tambahkan option tooltip untuk memastikan data yang benar ditampilkan
            'plugins' => [
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
        ];
    }
}