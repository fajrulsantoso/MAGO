<?php

namespace App\Filament\Widgets;

use App\Models\Auth\DosenPembimbingModel;
use App\Models\Reference\PenempatanMagangModel;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected int $totalPeriod = 14;

    protected function getStats(): array
    {    
        // Data saat ini
        $totalMahasiswa = PenempatanMagangModel::distinct('id_mahasiswa')->count('id_mahasiswa');
        $totalDosen = DosenPembimbingModel::count();
        $rasio = $totalDosen > 0 ? round($totalMahasiswa / $totalDosen, 2) : 0;
        
        // Data minggu lalu untuk perbandingan
        // $lastWeek = Carbon::now()->subWeek();
        // $lastWeek = Carbon::now()->subMonth();
        $lastWeek = Carbon::now()->subDays($this->totalPeriod);
        $totalMahasiswaLastWeek = PenempatanMagangModel::where('created_at', '<', $lastWeek)
            ->distinct('id_mahasiswa')
            ->count('id_mahasiswa');
        
        $totalDosenLastWeek = DosenPembimbingModel::where('created_at', '<', $lastWeek)->count();
        $rasioLastWeek = $totalDosenLastWeek > 0 ? round($totalMahasiswaLastWeek / $totalDosenLastWeek, 2) : 0;
        
        // Hitung persentase perubahan
        $mahasiswaPercentChange = $this->calculatePercentageChange($totalMahasiswaLastWeek, $totalMahasiswa);
        $dosenPercentChange = $this->calculatePercentageChange($totalDosenLastWeek, $totalDosen);
        $rasioPercentChange = $this->calculatePercentageChange($rasioLastWeek, $rasio);
        
        // Format deskripsi berdasarkan perubahan persentase
        [$mahasiswaDesc, $mahasiswaIcon, $mahasiswaColor] = $this->getDescriptionDetails($mahasiswaPercentChange);
        [$dosenDesc, $dosenIcon, $dosenColor] = $this->getDescriptionDetails($dosenPercentChange);
        [$rasioDesc, $rasioIcon, $rasioColor] = $this->getDescriptionDetails($rasioPercentChange, true);

        // Dapatkan data historis untuk chart
        $mahasiswaChart = $this->getMahasiswaChartData();
        $dosenChart = $this->getDosenChartData();
        $rasioChart = $this->getRasioChartData();

        return [
            Stat::make("Total Mahasiswa Magang (".$this->totalPeriod." hari)", $totalMahasiswa)
                ->description($mahasiswaDesc)
                ->descriptionIcon($mahasiswaIcon)
                ->color($mahasiswaColor)
                ->chart($mahasiswaChart),

            Stat::make("Dosen Pembimbing (".$this->totalPeriod." hari)", $totalDosen)
                ->description($dosenDesc)
                ->descriptionIcon($dosenIcon)
                ->color($dosenColor)
                ->chart($dosenChart),

            Stat::make("Rasio Dosen:Mahasiswa (".$this->totalPeriod." hari)", "1:$rasio")
                ->description($rasioDesc)
                ->descriptionIcon($rasioIcon)
                ->color($rasioColor)
                ->chart($rasioChart),
        ];
    }

    /**
     * Mendapatkan data chart untuk mahasiswa magang
     */
    private function getMahasiswaChartData(): array
    {
        $data = [];

        for ($i = $this->totalPeriod; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $nextDate = $i > 0 ? Carbon::now()->subDays($i-1)->startOfDay() : Carbon::now()->addDay()->startOfDay();
            
            // Jumlah mahasiswa per hari (mahasiswa yang mulai magang pada hari tersebut)
            $count = PenempatanMagangModel::where('created_at', '>=', $date)
                                         ->where('created_at', '<', $nextDate)
                                         ->distinct('id_mahasiswa')
                                         ->count('id_mahasiswa');
            
            // Sebagai alternatif: Jumlah kumulatif mahasiswa hingga hari tersebut
            // $count = PenempatanMagangModel::where('created_at', '<=', $date)->distinct('id_mahasiswa')->count('id_mahasiswa');
            
            $data[] = $count;
        }

        return $data;
    }
    
    /**
     * Mendapatkan data chart untuk dosen pembimbing
     */
    private function getDosenChartData(): array
    {
        $data = [];
        
        for ($i = $this->totalPeriod; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            
            // Jumlah dosen hingga tanggal tersebut
            $count = DosenPembimbingModel::where('created_at', '<=', $date)->count();
            
            $data[] = $count;
        }
        
        return $data;
    }
    
    /**
     * Mendapatkan data chart untuk rasio dosen:mahasiswa
     */
    private function getRasioChartData(): array
    {
        $data = [];
        
        for ($i = $this->totalPeriod; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            
            // Jumlah mahasiswa dan dosen hingga tanggal tersebut
            $mahasiswa = PenempatanMagangModel::where('created_at', '<=', $date)
                                             ->distinct('id_mahasiswa')
                                             ->count('id_mahasiswa');
            
            $dosen = DosenPembimbingModel::where('created_at', '<=', $date)->count();
            
            // Hitung rasio
            $ratio = $dosen > 0 ? round($mahasiswa / $dosen, 2) : 0;
            
            $data[] = $ratio;
        }
        
        return $data;
    }
    
    /**
     * Menghitung persentase perubahan antara nilai sebelumnya dan nilai saat ini.
     */
    private function calculatePercentageChange($oldValue, $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }
    
    /**
     * Menghasilkan deskripsi, ikon, dan warna berdasarkan persentase perubahan.
     * $isRatio digunakan untuk mengubah interpretasi angka pada rasio (nilai lebih rendah mungkin lebih baik)
     */
    private function getDescriptionDetails(float $percentChange, bool $isRatio = false): array
    {
        // Jika tidak ada perubahan signifikan (kurang dari 0.5%)
        if (abs($percentChange) < 0.5) {
            return ['Stabil', 'heroicon-m-minus', 'gray'];
        }
        
        // Jika rasio, interpretasi berbeda (rasio lebih rendah umumnya lebih baik)
        if ($isRatio) {
            // Jika rasio meningkat (lebih banyak mahasiswa per dosen - beban kerja meningkat)
            if ($percentChange > 0) {
                $desc = $percentChange . '% meningkat';
                return [$desc, 'heroicon-m-arrow-trending-up', 'warning'];
            } else {
                $desc = abs($percentChange) . '% membaik';
                return [$desc, 'heroicon-m-arrow-trending-down', 'success'];
            }
        }
        // Interpretasi umum (angka lebih tinggi lebih baik)
        else {
            if ($percentChange > 0) {
                $desc = $percentChange . '% meningkat';
                return [$desc, 'heroicon-m-arrow-trending-up', 'success'];
            } else {
                $desc = abs($percentChange) . '% menurun';
                return [$desc, 'heroicon-m-arrow-trending-down', 'danger'];
            }
        }
    }
}