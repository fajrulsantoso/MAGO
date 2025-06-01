<?php

namespace App\Filament\Widgets;

use App\Models\Reference\PengajuanMagangModel;
use App\Models\Reference\HistoriRekomendasiModel;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AdminAnalisisMinatPengajuanMahasiswaChart extends ChartWidget
{
    protected static ?string $heading = 'Analisis Minat Pengajuan Mahasiswa';
    protected static ?string $description = 'Perbandingan pengajuan magang berdasarkan rekomendasi vs tanpa melibatkan rekomendasi per bulan';
    // protected int|string|array $columnSpan = 'full';
    // protected static ?int $sort = 2; // Urutan widget setelah chart penempatan
    
    // 12 bulan terakhir
    protected function getMonths(): array 
    {
        $months = [];
        $labels = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('Y-m');
            $labels[] = $date->format('M Y'); 
        }
        
        return [
            'months' => $months,
            'labels' => $labels,
        ];
    }

    protected function getData(): array
    {
        $monthsData = $this->getMonths();
        $months = $monthsData['months'];
        $labels = $monthsData['labels'];
        
        $didalamRekomendasi = [];
        $diluarRekomendasi = [];

        foreach ($months as $month) {
            // status: diajukan, diterima, ditolak
            $pengajuans = PengajuanMagangModel::query()
                ->whereYear('tanggal_pengajuan', '=', substr($month, 0, 4))
                ->whereMonth('tanggal_pengajuan', '=', substr($month, 5, 2))
                ->select('id_mahasiswa', 'id_lowongan')
                ->get();
            
            $didalamCount = 0;
            $diluarCount = 0;
            
            // check lowongannya ada di histori rekomendasi mahasiswa atau tidak
            foreach ($pengajuans as $pengajuan) {
                // Cek apakah lowongan ini ada di histori rekomendasi mahasiswa
                $isRecommended = HistoriRekomendasiModel::where('id_mahasiswa', $pengajuan->id_mahasiswa)
                    ->where('id_lowongan', $pengajuan->id_lowongan)
                    ->exists();

                if ($isRecommended) {
                    $didalamCount++;
                } else {
                    $diluarCount++;
                }
            }

            $didalamRekomendasi[] = $didalamCount;
            $diluarRekomendasi[] = $diluarCount;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Berdasarkan Rekomendasi',
                    'data' => $didalamRekomendasi,
                    'borderColor' => '#3B82F6', // Warna biru
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.2, // Sedikit melengkung
                ],
                [
                    'label' => 'Tanpa Melibatkan Rekomendasi',
                    'data' => $diluarRekomendasi,
                    'borderColor' => '#F97316', // Warna oren
                    'backgroundColor' => 'rgba(249, 115, 22, 0.1)',
                    'tension' => 0.2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    // Konfigurasi tambahan untuk chart
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Jumlah Pengajuan',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Bulan',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
        ];
    }
}