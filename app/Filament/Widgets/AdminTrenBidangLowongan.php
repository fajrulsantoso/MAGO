<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AdminTrenBidangLowongan extends ChartWidget
{
    protected static ?string $heading = 'Tren Bidang Perusahaan Paling Diminati';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // Query untuk mendapatkan jumlah pengajuan per bidang keahlian
        $dataBidang = DB::table('m_bidang_keahlian as bk')
            ->join('r_lowongan_bidang as lb', 'bk.id_bidang', '=', 'lb.id_bidang')
            ->join('t_lowongan_magang as lm', 'lb.id_lowongan', '=', 'lm.id_lowongan')
            ->join('t_pengajuan_magang as pm', 'lm.id_lowongan', '=', 'pm.id_lowongan')
            ->select('bk.nama_bidang_keahlian', DB::raw('COUNT(pm.id_pengajuan) as jumlah_pengajuan'))
            ->groupBy('bk.nama_bidang_keahlian')
            ->orderByDesc('jumlah_pengajuan')
            ->limit(8)
            ->get();

        // Menghitung total pengajuan untuk perhitungan persentase
        $totalPengajuan = $dataBidang->sum('jumlah_pengajuan');

        // Format data dengan menambahkan peringkat pada label
        $labels = [];
        $values = [];
        
        foreach ($dataBidang as $index => $bidang) {
            // Hitung persentase
            $persentase = $totalPengajuan > 0 ? round(($bidang->jumlah_pengajuan / $totalPengajuan) * 100, 1) : 0;

            // Tambahkan nomor peringkat (1-based index)
            $labels[] = ($index + 1) . '. ' . $bidang->nama_bidang_keahlian . ' (' . $persentase . '%)';
            $values[] = $bidang->jumlah_pengajuan;
        }

        $colors = [
            '#60A5FA', // Biru Muda - Peringkat #1 (paling terang)
            '#3B82F6', // Biru Medium - Peringkat #2
            '#2563EB', // Biru - Peringkat #3
            '#1D4ED8', // Biru Tua - Peringkat #4
            '#1E40AF', // Biru Sangat Tua - Peringkat #5
            '#1E3A8A', // Biru Navy - Peringkat #6
            '#172554', // Biru Gelap - Peringkat #7
            '#0F172A', // Biru Hampir Hitam - Peringkat #8
        ];

        return [
            'datasets' => [
                [
                    // 'label' => 'Jumlah Pengajuan',
                    'data' => $values,
                    'backgroundColor' => array_slice($colors, 0, count($values)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'font' => [
                            'size' => 12,
                        ],
                        'padding' => 20,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.label + ': ' + context.parsed + ' pengajuan';
                        }",
                    ],
                ],
                'datalabels' => [
                    'display' => true,
                    'color' => '#fff',
                    'font' => [
                        'weight' => 'bold',
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}