<?php

namespace App\Filament\Widgets;

use App\Models\Reference\PenempatanMagangModel;
use App\Models\Reference\HistoriRekomendasiModel; 
use App\Models\Reference\PengajuanMagangModel;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AdminAnalisisPenempatanMagangMahasiswaChart extends ChartWidget
{
    protected static ?string $heading = 'Analisis Penempatan Magang Mahasiswa';
    protected static ?string $description = 'Perbandingan penempatan magang berdasarkan rekomendasi vs tanpa melibatkan rekomendasi sistem per bulan';
    // protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = -1; // Urutan widget

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
            
            $penempatans = PenempatanMagangModel::query()
                ->join('t_pengajuan_magang', 't_penempatan_magang.id_pengajuan', '=', 't_pengajuan_magang.id_pengajuan')
                ->whereYear('t_penempatan_magang.created_at', '=', substr($month, 0, 4))
                ->whereMonth('t_penempatan_magang.created_at', '=', substr($month, 5, 2))
                ->select('t_penempatan_magang.id_mahasiswa', 't_pengajuan_magang.id_lowongan')
                ->get();
            
            $didalamCount = 0;
            $diluarCount = 0;
            
            // check apakah lowongannya ada di histori rekomendasi mahasiswa atau tidak
            foreach ($penempatans as $penempatan) {
                // Cek apakah lowongan ini ada di histori rekomendasi mahasiswa
                $isRecommended = HistoriRekomendasiModel::where('id_mahasiswa', $penempatan->id_mahasiswa)
                    ->where('id_lowongan', $penempatan->id_lowongan)
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
                    'borderColor' => '#10B981', // Warna hijau
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Tanpa Melibatkan Rekomendasi',
                    'data' => $diluarRekomendasi,
                    'borderColor' => '#EF4444', // Warna merah
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
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
                        'text' => 'Jumlah Penempatan',
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