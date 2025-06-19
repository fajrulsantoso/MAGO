<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Models\Auth\MahasiswaModel;
use App\Models\Reference\LowonganMagangModel;
use App\Models\Reference\PreferensiMahasiswaModel;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class RekomendasiMagang extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = '🎗️ Rekomendasi Magang';
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists();
    }
    
    protected array $bobot = [
        1 => 0.397,
        2 => 0.297,
        3 => 0.178,
        4 => 0.089,
        5 => 0.038
    ];

    public function table(Table $table): Table
    {
        $rekomendasiCollection = $this->getRekomendasi();

        return $table
            ->query(function () use ($rekomendasiCollection) {
                if (empty($rekomendasiCollection)) {
                    return LowonganMagangModel::whereRaw('1 = 0');
                }

                $orderedIds = $rekomendasiCollection->pluck('id_lowongan')->toArray();
                if (empty($orderedIds)) {
                    return LowonganMagangModel::whereRaw('1 = 0');
                }

                return LowonganMagangModel::query()
                    ->with(['jenisMagang', 'daerahMagang', 'waktuMagang', 'insentif', 'perusahaan'])
                    ->whereIn('id_lowongan', $orderedIds)
                    ->orderByRaw("FIELD(id_lowongan, " . implode(',', $orderedIds) . ")");
            })
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('judul_lowongan')
                        ->searchable()
                        ->copyable()
                        ->label('Judul Lowongan')
                        ->weight('bold')
                        ->size('lg')
                        ->formatStateUsing(function ($state, $record) use ($rekomendasiCollection) {
                            $position = $rekomendasiCollection->search(function ($item) use ($record) {
                                return $item->id_lowongan === $record->id_lowongan;
                            });

                            $position = $position !== false ? $position + 1 : null;

                            $medal = match ((int)$position) {
                                1 => '<span class="text-amber-500 text-xl mr-1">🥇</span>',
                                2 => '<span class="text-gray-500 text-xl mr-1">🥈</span>',
                                3 => '<span class="text-orange-600 text-xl mr-1">🥉</span>',
                                default => '',
                            };

                            return new HtmlString($medal . $state);
                        })
                        ->tooltip(fn($record) => new HtmlString($record->judul_lowongan)),

                    Tables\Columns\TextColumn::make('perusahaan.nama')
                        ->label('Perusahaan')
                        ->icon('heroicon-o-building-office')
                        ->searchable()
                        ->weight('medium'),

                    Tables\Columns\TextColumn::make('daerahMagang.namaLengkapDenganProvinsi')
                        ->label('Lokasi')
                        ->icon('heroicon-o-map-pin')
                        ->weight('medium'),

                    Tables\Columns\TextColumn::make('jenisMagang.nama_jenis_magang')
                        ->label('Jenis Magang')
                        ->icon('heroicon-o-academic-cap')
                        ->searchable()
                        ->badge()
                        ->color('success'),

                    Tables\Columns\Layout\Grid::make([
                        'default' => 1,
                        'sm' => 4,
                    ])
                        ->schema([
                            Tables\Columns\TextColumn::make('waktuMagang.waktu_magang')
                                ->label('Waktu Magang')
                                ->icon('heroicon-o-clock')
                                ->searchable()
                                ->badge()
                                ->color('info'),

                            Tables\Columns\TextColumn::make('insentif.keterangan')
                                ->label('Insentif')
                                ->icon('heroicon-o-banknotes')
                                ->searchable()
                                ->badge()
                                ->color('warning'),
                        ]),
                ])
                    ->extraAttributes(['class' => 'space-y-2'])
                    ->grow(),

                Tables\Columns\TextColumn::make('daerahMagang.nama_daerah')
                    ->label('Nama Daerah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->extraAttributes(['class' => 'hidden'])
                    ->extraHeaderAttributes(['class' => 'hidden']),

                Tables\Columns\TextColumn::make('daerahMagang.jenis_daerah')
                    ->label('Jenis Daerah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->extraAttributes(['class' => 'hidden'])
                    ->extraHeaderAttributes(['class' => 'hidden']),
            ])
            ->paginationPageOptions([9, 18, 27, 'all'])
            ->striped()
            ->emptyStateHeading('Belum ada lowongan yang tersedia')
            ->emptyStateIcon('heroicon-o-exclamation-circle')
            ->filters([
                Tables\Filters\SelectFilter::make('id_jenis_magang')
                    ->label('Jenis Magang')
                    ->relationship('jenisMagang', 'nama_jenis_magang'),
                Tables\Filters\SelectFilter::make('id_daerah_magang')
                    ->label('Daerah Magang')
                    ->relationship('daerahMagang', 'nama_daerah'),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat_detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(
                        fn(LowonganMagangModel $record) =>
                        '/mahasiswa/lowongan-magang/' . $record->id_lowongan
                    )
                    ->openUrlInNewTab(false)
                    ->color('primary')
                    ->button(),
                Tables\Actions\Action::make('lihat_perhitungan')
                    ->label('Lihat Perhitungan')
                    ->icon('heroicon-o-calculator')
                    ->color('gray')
                    ->modalWidth('7xl')
                    ->modalHeading('Detail Perhitungan Rekomendasi')
                    ->modalDescription('Berikut adalah detail perhitungan rekomendasi menggunakan metode SWARA DAN ARAS')
                    ->action(function (LowonganMagangModel $record) {})
                    ->modalContent(function (LowonganMagangModel $record) {
                        return new HtmlString($this->generatePerhitunganHtml($record->id_lowongan));
                    })
                    ->button()
            ])
            ->contentGrid([
                'default' => 1,
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 3,
            ]);
    }

    protected function getRekomendasi(): Collection
    {
        $mahasiswa = MahasiswaModel::where('id_user', Auth::id())->first();
        if (!$mahasiswa) {
            return collect([]);
        }

        $preferensi = PreferensiMahasiswaModel::where('id_mahasiswa', $mahasiswa->id_mahasiswa)->first();
        if (!$preferensi) {
            return collect([]);
        }

        // Mendapatkan lowongan yang aktif
        $lowonganCollection = LowonganMagangModel::query()
            ->with(['bidangKeahlian', 'jenisMagang', 'daerahMagang', 'waktuMagang', 'insentif', 'perusahaan'])
            ->where('status', 'Aktif')
            ->whereHas('jenisMagang', function ($query) {
                $query->where('nama_jenis_magang', '!=', 'Magang Mandiri');
            })
            ->get();

        if ($lowonganCollection->isEmpty()) {
            return collect([]);
        }

        // Mendapatkan preferensi jenis magang dan bidang keahlian
        $preferensiJenisMagang = $preferensi->jenisMagang->pluck('id_jenis_magang')->toArray();


        // Membuat matriks keputusan (matrix)
        $matrix = [];
        $lowonganIds = [];

        // STEP 1: Hitung nilai untuk semua alternatif (lowongan)
        foreach ($lowonganCollection as $index => $lowongan) {
            $rowIndex = $index + 1;
            $lowonganIds[$rowIndex] = $lowongan->id_lowongan;

            // Menghitung kesesuaian daerah
            $daerahPreferensi = $preferensi->daerahMagang;
            $daerahLowongan = $lowongan->daerahMagang;

            $earthRadius = 6371; // Radius Bumi dalam kilometer

            if (
                $daerahPreferensi->latitude == $daerahLowongan->latitude &&
                $daerahPreferensi->longitude == $daerahLowongan->longitude
            ) {
                $daerah = 1;
            } else {
                $lat1 = deg2rad($daerahPreferensi->latitude);
                $lon1 = deg2rad($daerahPreferensi->longitude);
                $lat2 = deg2rad($daerahLowongan->latitude);
                $lon2 = deg2rad($daerahLowongan->longitude);

                // Haversine formula
                $latDelta = $lat2 - $lat1;
                $lonDelta = $lon2 - $lon1;
                $a = sin($latDelta / 2) ** 2 + cos($lat1) * cos($lat2) * sin($lonDelta / 2) ** 2;
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $daerah = $earthRadius * $c;
            }

            // Menghitung kesesuaian waktu
            $waktu = ($lowongan->id_waktu_magang == $preferensi->id_waktu_magang) ? 1 : 0;

            // Menghitung kesesuaian insentif
            $insentif = ($lowongan->id_insentif == $preferensi->id_insentif) ? 1 : 0;

            // Menghitung kesesuaian jenis magang
            $jenis = in_array($lowongan->id_jenis_magang, $preferensiJenisMagang) ? 1 : 0;

            // Menghitung kesesuaian bidang keahlian
            $lowonganBidang = $lowongan->bidangKeahlian->pluck('id_bidang')->toArray();
            $preferensiBidang = $preferensi->bidangKeahlian->pluck('id_bidang')->toArray();
            $bidangCount = count($lowonganBidang);
            $matchCount = 0;

            if ($bidangCount > 0) {
                foreach ($lowonganBidang as $bidang) {
                    if (in_array($bidang, $preferensiBidang)) {
                        $matchCount++;
                    }
                }
                $bidang = $matchCount;
            } else {
                $bidang = 0;
            }

            $matrix[$rowIndex] = [
                'daerah' => $daerah,
                'waktu' => $waktu,
                'insentif' => $insentif,
                'jenis' => $jenis,
                'bidang' => $bidang,
            ];
        }

        // STEP 2: Temukan nilai optimal untuk setiap kriteria
        $maxBidang = 0;
        $minDaerah = PHP_INT_MAX;

        foreach ($matrix as $i => $row) {
            if ($row['bidang'] > $maxBidang) {
                $maxBidang = $row['bidang'];
            }
            if ($row['daerah'] < $minDaerah) {
                $minDaerah = $row['daerah'];
            } else if ($row['daerah'] == 0) {
                $minDaerah = 1;
            }
        }

        // Tambahkan alternatif optimal sebagai baris pertama (A0)
        $matrix[0] = [
            'daerah' => $minDaerah,  // Cost
            'waktu' => 1,    // Benefit
            'insentif' => 1, // Benefit
            'jenis' => 1,    // Benefit
            'bidang' => $maxBidang,  // Benefit
        ];

        // Menghitung jumlah setiap kolom untuk normalisasi
        $colSums = [
            'waktu' => 0,
            'insentif' => 0,
            'jenis' => 0,
            'bidang' => 0,
        ];

        $invertedDaerah = [];
        $sumInvertedDaerah = 0;

        foreach ($matrix as $i => $row) {
            $invertedDaerah[$i] = 1 / $row['daerah'];

            $sumInvertedDaerah += $invertedDaerah[$i];

            $colSums['waktu'] += $row['waktu'];
            $colSums['insentif'] += $row['insentif'];
            $colSums['jenis'] += $row['jenis'];
            $colSums['bidang'] += $row['bidang'];
        }

        // Normalisasi matriks
        $normalizedMatrix = [];
        foreach ($matrix as $i => $row) {
            $normalizedMatrix[$i] = [
                'daerah' => ($sumInvertedDaerah > 0) ? $invertedDaerah[$i] / $sumInvertedDaerah : 0,
                'waktu' => ($colSums['waktu'] > 0) ? $row['waktu'] / $colSums['waktu'] : 0,
                'insentif' => ($colSums['insentif'] > 0) ? $row['insentif'] / $colSums['insentif'] : 0,
                'jenis' => ($colSums['jenis'] > 0) ? $row['jenis'] / $colSums['jenis'] : 0,
                'bidang' => ($colSums['bidang'] > 0) ? $row['bidang'] / $colSums['bidang'] : 0,
            ];
        }

        // Normalisasi dengan bobot
        $weightedMatrix = [];
        $weightedMatrix[0] = [
            'daerah' => $normalizedMatrix[0]['daerah'] * $this->bobot[$preferensi->ranking_daerah],
            'waktu' => $normalizedMatrix[0]['waktu'] * $this->bobot[$preferensi->ranking_waktu_magang],
            'insentif' => $normalizedMatrix[0]['insentif'] * $this->bobot[$preferensi->ranking_insentif],
            'jenis' => $normalizedMatrix[0]['jenis'] * $this->bobot[$preferensi->ranking_jenis_magang],
            'bidang' => $normalizedMatrix[0]['bidang'] * $this->bobot[$preferensi->ranking_bidang],
        ];

        // Hitung nilai S dan K untuk setiap alternatif
        $optimalValue = array_sum($weightedMatrix[0]);
        $sValues = [];
        $kValues = [];

        for ($i = 1; $i < count($normalizedMatrix); $i++) {
            $weightedMatrix[$i] = [
                'daerah' => $normalizedMatrix[$i]['daerah'] * $this->bobot[$preferensi->ranking_daerah],
                'waktu' => $normalizedMatrix[$i]['waktu'] * $this->bobot[$preferensi->ranking_waktu_magang],
                'insentif' => $normalizedMatrix[$i]['insentif'] * $this->bobot[$preferensi->ranking_insentif],
                'jenis' => $normalizedMatrix[$i]['jenis'] * $this->bobot[$preferensi->ranking_jenis_magang],
                'bidang' => $normalizedMatrix[$i]['bidang'] * $this->bobot[$preferensi->ranking_bidang],
            ];

            $sValues[$i] = array_sum($weightedMatrix[$i]);
            $kValues[$i] = ($optimalValue > 0) ? $sValues[$i] / $optimalValue : 0;
        }

        // Persiapkan hasil rekomendasi
        $rekomendasiItems = [];
        foreach ($kValues as $i => $kValue) {
            $lowonganId = $lowonganIds[$i];
            $lowongan = $lowonganCollection->firstWhere('id_lowongan', $lowonganId);
            if ($lowongan) {
                $rekomendasiItems[] = [
                    'id_lowongan' => $lowonganId,
                    'kesesuaian' => $kValue,
                    'lowongan' => $lowongan,
                ];
            }
        }

        // Urutkan hasil rekomendasi berdasarkan nilai kesesuaian (tertinggi ke terendah)
        usort($rekomendasiItems, function ($a, $b) {
            return $b['kesesuaian'] <=> $a['kesesuaian'];
        });

        $this->saveTopRecommendations($rekomendasiItems, $mahasiswa->id_mahasiswa, $preferensi->id_preferensi);

        // Kembalikan hasil sebagai collection yang sudah diurutkan
        $lowonganCollectionSorted = collect(array_map(function ($item) {
            return $item['lowongan'];
        }, $rekomendasiItems));

        return $lowonganCollectionSorted;
    }

    protected function saveTopRecommendations($recommendations, $mahasiswaId, $preferensiId)
    {
        if (empty($recommendations)) {
            return;
        }

        // Ambil hanya 10 besar
        $topTen = array_slice($recommendations, 0, 10);

        // Hapus data lama
        DB::table('t_histori_rekomendasi')
            ->where('id_mahasiswa', $mahasiswaId)
            ->where('id_preferensi', $preferensiId)
            ->delete();

        // Simpan data baru
        $dataToInsert = [];
        foreach ($topTen as $index => $item) {
            $ranking = $index + 1;

            $dataToInsert[] = [
                'id_mahasiswa' => $mahasiswaId,
                'id_lowongan' => $item['id_lowongan'],
                'id_preferensi' => $preferensiId,
                'ranking' => $ranking,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert batch data
        if (!empty($dataToInsert)) {
            DB::table('t_histori_rekomendasi')->insert($dataToInsert);
        }
    }

    protected function generatePerhitunganHtml($idLowongan): string
    {
        $mahasiswa = MahasiswaModel::where('id_user', Auth::id())->first();
        if (!$mahasiswa) {
            return '<p>Data mahasiswa tidak ditemukan</p>';
        }

        $preferensi = PreferensiMahasiswaModel::where('id_mahasiswa', $mahasiswa->id_mahasiswa)->first();
        if (!$preferensi) {
            return '<p>Data preferensi tidak ditemukan</p>';
        }

        // Mendapatkan lowongan yang aktif
        $lowonganCollection = LowonganMagangModel::query()
            ->with(['bidangKeahlian', 'jenisMagang', 'daerahMagang', 'waktuMagang', 'insentif', 'perusahaan'])
            ->where('status', 'Aktif')
            ->whereHas('jenisMagang', function ($query) {
                $query->where('nama_jenis_magang', '!=', 'Magang Mandiri');
            })
            ->get();

        if ($lowonganCollection->isEmpty()) {
            return '<p>Tidak ada data lowongan</p>';
        }

        // Preferensi jenis magang dan bidang keahlian
        $preferensiJenisMagang = $preferensi->jenisMagang->pluck('id_jenis_magang')->toArray();

        // Membuat matriks keputusan (matrix)
        $matrix = [];
        $lowonganIds = [];
        $lowonganTitles = [];

        // Menghitung nilai untuk semua alternatif (lowongan)
        foreach ($lowonganCollection as $index => $lowongan) {
            $rowIndex = $index + 1;
            $lowonganIds[$rowIndex] = $lowongan->id_lowongan;
            $lowonganTitles[$rowIndex] = $lowongan->judul_lowongan;

            // Menghitung kesesuaian daerah
            $daerahPreferensi = $preferensi->daerahMagang;
            $daerahLowongan = $lowongan->daerahMagang;

            $earthRadius = 6371; // Radius Bumi dalam kilometer

            if (
                $daerahPreferensi->latitude == $daerahLowongan->latitude &&
                $daerahPreferensi->longitude == $daerahLowongan->longitude
            ) {
                $daerah = 1;
            } else {
                $lat1 = deg2rad($daerahPreferensi->latitude);
                $lon1 = deg2rad($daerahPreferensi->longitude);
                $lat2 = deg2rad($daerahLowongan->latitude);
                $lon2 = deg2rad($daerahLowongan->longitude);

                // Haversine formula
                $latDelta = $lat2 - $lat1;
                $lonDelta = $lon2 - $lon1;
                $a = sin($latDelta / 2) ** 2 + cos($lat1) * cos($lat2) * sin($lonDelta / 2) ** 2;
                $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                $daerah = $earthRadius * $c;
            }

            // Menghitung kesesuaian waktu
            $waktu = ($lowongan->id_waktu_magang == $preferensi->id_waktu_magang) ? 1 : 0;

            // Menghitung kesesuaian insentif
            $insentif = ($lowongan->id_insentif == $preferensi->id_insentif) ? 1 : 0;

            // Menghitung kesesuaian jenis magang
            $jenis = in_array($lowongan->id_jenis_magang, $preferensiJenisMagang) ? 1 : 0;

            // Menghitung kesesuaian bidang keahlian
            $lowonganBidang = $lowongan->bidangKeahlian->pluck('id_bidang')->toArray();
            $preferensiBidang = $preferensi->bidangKeahlian->pluck('id_bidang')->toArray();
            $bidangCount = count($lowonganBidang);
            $matchCount = 0;

            if ($bidangCount > 0) {
                foreach ($lowonganBidang as $bidang) {
                    if (in_array($bidang, $preferensiBidang)) {
                        $matchCount++;
                    }
                }
                $bidang = $matchCount;
            } else {
                $bidang = 0;
            }

            $matrix[$rowIndex] = [
                'daerah' => $daerah,
                'waktu' => $waktu,
                'insentif' => $insentif,
                'jenis' => $jenis,
                'bidang' => $bidang,
            ];
        }

        // Nilai optimal untuk setiap kriteria (A0)
        $maxBidang = 0;
        $minDaerah = PHP_INT_MAX;

        foreach ($matrix as $i => $row) {
            if ($row['bidang'] > $maxBidang) {
                $maxBidang = $row['bidang'];
            }
            if ($row['daerah'] < $minDaerah) {
                $minDaerah = $row['daerah'];
            } else if ($row['daerah'] == 0) {
                $minDaerah = 1;
            }
        }

        // Tambahkan alternatif optimal sebagai baris pertama (A0)
        $matrix[0] = [
            'daerah' => $minDaerah,  // Cost
            'waktu' => 1,    // Benefit
            'insentif' => 1, // Benefit
            'jenis' => 1,    // Benefit
            'bidang' => $maxBidang,  // Benefit
        ];

        // Menghitung jumlah setiap kolom untuk normalisasi
        $colSums = [
            'waktu' => 0,
            'insentif' => 0,
            'jenis' => 0,
            'bidang' => 0,
        ];

        $invertedDaerah = [];
        $sumInvertedDaerah = 0;

        foreach ($matrix as $i => $row) {
            $invertedDaerah[$i] = 1 / $row['daerah'];

            $sumInvertedDaerah += $invertedDaerah[$i];

            $colSums['waktu'] += $row['waktu'];
            $colSums['insentif'] += $row['insentif'];
            $colSums['jenis'] += $row['jenis'];
            $colSums['bidang'] += $row['bidang'];
        }

        // Normalisasi matriks
        $normalizedMatrix = [];
        foreach ($matrix as $i => $row) {
            $normalizedMatrix[$i] = [
                'daerah' => ($sumInvertedDaerah > 0) ? $invertedDaerah[$i] / $sumInvertedDaerah : 0,
                'waktu' => ($colSums['waktu'] > 0) ? $row['waktu'] / $colSums['waktu'] : 0,
                'insentif' => ($colSums['insentif'] > 0) ? $row['insentif'] / $colSums['insentif'] : 0,
                'jenis' => ($colSums['jenis'] > 0) ? $row['jenis'] / $colSums['jenis'] : 0,
                'bidang' => ($colSums['bidang'] > 0) ? $row['bidang'] / $colSums['bidang'] : 0,
            ];
        }

        // Normalisasi dengan bobot
        $weightedMatrix = [];
        $weightedMatrix[0] = [
            'daerah' => $normalizedMatrix[0]['daerah'] * $this->bobot[$preferensi->ranking_daerah],
            'waktu' => $normalizedMatrix[0]['waktu'] * $this->bobot[$preferensi->ranking_waktu_magang],
            'insentif' => $normalizedMatrix[0]['insentif'] * $this->bobot[$preferensi->ranking_insentif],
            'jenis' => $normalizedMatrix[0]['jenis'] * $this->bobot[$preferensi->ranking_jenis_magang],
            'bidang' => $normalizedMatrix[0]['bidang'] * $this->bobot[$preferensi->ranking_bidang],
        ];

        // Hitung nilai S dan K untuk setiap alternatif
        $optimalValue = array_sum($weightedMatrix[0]);
        $sValues = [];
        $kValues = [];

        for ($i = 1; $i < count($normalizedMatrix); $i++) {
            $weightedMatrix[$i] = [
                'daerah' => $normalizedMatrix[$i]['daerah'] * $this->bobot[$preferensi->ranking_daerah],
                'waktu' => $normalizedMatrix[$i]['waktu'] * $this->bobot[$preferensi->ranking_waktu_magang],
                'insentif' => $normalizedMatrix[$i]['insentif'] * $this->bobot[$preferensi->ranking_insentif],
                'jenis' => $normalizedMatrix[$i]['jenis'] * $this->bobot[$preferensi->ranking_jenis_magang],
                'bidang' => $normalizedMatrix[$i]['bidang'] * $this->bobot[$preferensi->ranking_bidang],
            ];

            $sValues[$i] = array_sum($weightedMatrix[$i]);
            $kValues[$i] = ($optimalValue > 0) ? $sValues[$i] / $optimalValue : 0;
        }

        // Persiapkan hasil rekomendasi
        $rekomendasiItems = [];
        foreach ($kValues as $i => $kValue) {
            $lowonganId = $lowonganIds[$i];
            $lowongan = $lowonganCollection->firstWhere('id_lowongan', $lowonganId);
            if ($lowongan) {
                $rekomendasiItems[] = [
                    'id_lowongan' => $lowonganId,
                    'judul_lowongan' => $lowonganTitles[$i],
                    'kesesuaian' => $kValue,
                    'nilai_s' => $sValues[$i],
                    'matrix' => $matrix[$i],
                    'normalized' => $normalizedMatrix[$i],
                    'weighted' => $weightedMatrix[$i],
                ];
            }
        }

        // Urutkan hasil rekomendasi berdasarkan nilai kesesuaian (tertinggi ke terendah)
        usort($rekomendasiItems, function ($a, $b) {
            return $b['kesesuaian'] <=> $a['kesesuaian'];
        });

        // Generate HTML
        $html = '<div class="text-sm">';

        // Tampilkan info preferensi
        $html .= '<div class="mb-6 bg-blue-50 p-4 rounded-lg">';
        $html .= '<h3 class="font-bold text-lg mb-2">Preferensi Mahasiswa</h3>';
        $html .= '<div class="grid grid-cols-2 gap-4">';
        $html .= '<div><span class="font-medium">Lokasi Preferensi:</span> ' . $preferensi->daerahMagang->namaLengkapDenganProvinsi . '</div>';
        $html .= '<div><span class="font-medium">Waktu Magang:</span> ' . $preferensi->waktuMagang->waktu_magang . '</div>';
        $html .= '<div><span class="font-medium">Insentif:</span> ' . $preferensi->insentif->keterangan . '</div>';
        $html .= '<div><span class="font-medium">Jenis Magang:</span> ' . implode(', ', $preferensi->jenisMagang->pluck('nama_jenis_magang')->toArray()) . '</div>';
        $html .= '<div><span class="font-medium">Bidang Keahlian:</span> ' . implode(', ', $preferensi->bidangKeahlian->pluck('nama_bidang_keahlian')->toArray()) . '</div>';
        $html .= '</div>';

        // Tampilkan bobot
        $html .= '<div class="mt-2">';
        $html .= '<h4 class="font-bold my-1">Bobot Kriteria (berdasarkan ranking preferensi):</h4>';
        $html .= '<ul class="list-disc list-inside">';
        $html .= '<li>Daerah (#' . $preferensi->ranking_daerah . '): ' . $this->bobot[$preferensi->ranking_daerah] . '</li>';
        $html .= '<li>Waktu Magang (#' . $preferensi->ranking_waktu_magang . '): ' . $this->bobot[$preferensi->ranking_waktu_magang] . '</li>';
        $html .= '<li>Insentif (#' . $preferensi->ranking_insentif . '): ' . $this->bobot[$preferensi->ranking_insentif] . '</li>';
        $html .= '<li>Jenis Magang (#' . $preferensi->ranking_jenis_magang . '): ' . $this->bobot[$preferensi->ranking_jenis_magang] . '</li>';
        $html .= '<li>Bidang Keahlian (#' . $preferensi->ranking_bidang . '): ' . $this->bobot[$preferensi->ranking_bidang] . '</li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';

        // Matriks keputusan awal
        $html .= '<h3 class="font-bold text-lg mb-2">1. Matriks Keputusan</h3>';
        $html .= '<div class="overflow-x-auto mb-6">';
        $html .= '<table class="w-full border-collapse border border-gray-300">';
        $html .= '<thead class="bg-blue-100">';
        $html .= '<tr>';
        $html .= '<th class="border border-gray-300 p-2">Alternatif</th>';
        $html .= '<th class="border border-gray-300 p-2">Daerah (km)</th>';
        $html .= '<th class="border border-gray-300 p-2">Waktu</th>';
        $html .= '<th class="border border-gray-300 p-2">Insentif</th>';
        $html .= '<th class="border border-gray-300 p-2">Jenis</th>';
        $html .= '<th class="border border-gray-300 p-2">Bidang</th>';
        $html .= '</tr>';
        $html .= '</thead><tbody>';

        // Row untuk A0 (nilai optimal)
        $html .= '<tr class="bg-blue-50">';
        $html .= '<td class="border border-gray-300 p-2 font-medium">A0 (Optimal)</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($matrix[0]['daerah'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . $matrix[0]['waktu'] . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . $matrix[0]['insentif'] . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . $matrix[0]['jenis'] . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . $matrix[0]['bidang'] . '</td>';
        $html .= '</tr>';

        // Highlight lowongan yang dipilih
        foreach ($rekomendasiItems as $index => $item) {
            $isHighlighted = ($item['id_lowongan'] == $idLowongan);
            $rowClass = $isHighlighted ? 'bg-yellow-100' : '';

            $i = array_search($item['id_lowongan'], $lowonganIds);
            if ($i !== false) {
                $html .= '<tr class="' . $rowClass . '">';
                $html .= '<td class="border border-gray-300 p-2 font-medium">A' . $i . ' ' .
                    ($isHighlighted ? '(Lowongan ini) ✓' : '') .
                    '<div class="text-xs">' . $item['judul_lowongan'] . '</div></td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($matrix[$i]['daerah'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . $matrix[$i]['waktu'] . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . $matrix[$i]['insentif'] . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . $matrix[$i]['jenis'] . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . $matrix[$i]['bidang'] . '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table>';
        $html .= '</div>';

        // Matriks ternormalisasi
        $html .= '<h3 class="font-bold text-lg mb-2">2. Matriks Ternormalisasi</h3>';
        $html .= '<div class="overflow-x-auto mb-6">';
        $html .= '<table class="w-full border-collapse border border-gray-300">';
        $html .= '<thead class="bg-blue-100">';
        $html .= '<tr>';
        $html .= '<th class="border border-gray-300 p-2">Alternatif</th>';
        $html .= '<th class="border border-gray-300 p-2">Daerah</th>';
        $html .= '<th class="border border-gray-300 p-2">Waktu</th>';
        $html .= '<th class="border border-gray-300 p-2">Insentif</th>';
        $html .= '<th class="border border-gray-300 p-2">Jenis</th>';
        $html .= '<th class="border border-gray-300 p-2">Bidang</th>';
        $html .= '</tr>';
        $html .= '</thead><tbody>';

        // Row untuk A0 (nilai optimal)
        $html .= '<tr class="bg-blue-50">';
        $html .= '<td class="border border-gray-300 p-2 font-medium">A0 (Optimal)</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[0]['daerah'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[0]['waktu'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[0]['insentif'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[0]['jenis'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[0]['bidang'], 3) . '</td>';
        $html .= '</tr>';

        foreach ($rekomendasiItems as $index => $item) {
            $isHighlighted = ($item['id_lowongan'] == $idLowongan);
            $rowClass = $isHighlighted ? 'bg-yellow-100' : '';

            $i = array_search($item['id_lowongan'], $lowonganIds);
            if ($i !== false) {
                $html .= '<tr class="' . $rowClass . '">';
                $html .= '<td class="border border-gray-300 p-2 font-medium">A' . $i . ' ' .
                    ($isHighlighted ? '(Lowongan ini) ✓' : '') . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[$i]['daerah'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[$i]['waktu'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[$i]['insentif'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[$i]['jenis'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($normalizedMatrix[$i]['bidang'], 3) . '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table>';
        $html .= '</div>';

        // Matriks terbobot
        $html .= '<h3 class="font-bold text-lg mb-2">3. Matriks Terbobot</h3>';
        $html .= '<div class="overflow-x-auto mb-6">';
        $html .= '<table class="w-full border-collapse border border-gray-300">';
        $html .= '<thead class="bg-blue-100">';
        $html .= '<tr>';
        $html .= '<th class="border border-gray-300 p-2">Alternatif</th>';
        $html .= '<th class="border border-gray-300 p-2">Daerah</th>';
        $html .= '<th class="border border-gray-300 p-2">Waktu</th>';
        $html .= '<th class="border border-gray-300 p-2">Insentif</th>';
        $html .= '<th class="border border-gray-300 p-2">Jenis</th>';
        $html .= '<th class="border border-gray-300 p-2">Bidang</th>';
        $html .= '<th class="border border-gray-300 p-2">Nilai S</th>';
        $html .= '<th class="border border-gray-300 p-2">Nilai K</th>';
        $html .= '<th class="border border-gray-300 p-2">Ranking</th>';
        $html .= '</tr>';
        $html .= '</thead><tbody>';

        // Row untuk A0 (nilai optimal)
        $html .= '<tr class="bg-blue-50">';
        $html .= '<td class="border border-gray-300 p-2 font-medium">A0 (Optimal)</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[0]['daerah'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[0]['waktu'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[0]['insentif'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[0]['jenis'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[0]['bidang'], 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2 font-medium">' . number_format($optimalValue, 3) . '</td>';
        $html .= '<td class="border border-gray-300 p-2 font-medium">1.0000</td>';
        $html .= '<td class="border border-gray-300 p-2 font-medium">-</td>';
        $html .= '</tr>';

        // Sorting by K value
        $rekomendasiItems = collect($rekomendasiItems)->sortByDesc('kesesuaian')->values()->all();

        foreach ($rekomendasiItems as $rank => $item) {
            $isHighlighted = ($item['id_lowongan'] == $idLowongan);
            $rowClass = $isHighlighted ? 'bg-yellow-100' : '';

            $i = array_search($item['id_lowongan'], $lowonganIds);
            if ($i !== false) {
                $html .= '<tr class="' . $rowClass . '">';
                $html .= '<td class="border border-gray-300 p-2 font-medium">A' . $i . ' ' .
                    ($isHighlighted ? '(Lowongan ini) ✓' : '') . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[$i]['daerah'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[$i]['waktu'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[$i]['insentif'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[$i]['jenis'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2">' . number_format($weightedMatrix[$i]['bidang'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2 font-medium">' . number_format($item['nilai_s'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2 font-medium">' . number_format($item['kesesuaian'], 3) . '</td>';
                $html .= '<td class="border border-gray-300 p-2 font-medium">' . ($rank + 1) . '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</tbody></table>';
        $html .= '</div>';

        $html .= '<div class="text-sm mt-4 p-4 bg-blue-50 rounded-lg">';
        $html .= '<h3 class="font-bold">Keterangan:</h3>';
        $html .= '<ul class="list-disc list-inside">';
        $html .= '<li><b>Daerah:</b> Jarak antara preferensi dan lokasi lowongan (KM). Semakin kecil nilainya semakin baik (cost)</li>';
        $html .= '<li><b>Waktu:</b> 1 jika sesuai dengan preferensi, 0 jika tidak</li>';
        $html .= '<li><b>Insentif:</b> 1 jika sesuai dengan preferensi, 0 jika tidak</li>';
        $html .= '<li><b>Jenis:</b> 1 jika sesuai dengan preferensi, 0 jika tidak</li>';
        $html .= '<li><b>Bidang:</b> Jumlah bidang keahlian yang cocok dengan preferensi</li>';
        $html .= '<li><b>Nilai S:</b> Jumlah nilai terbobot dari semua kriteria</li>';
        $html .= '<li><b>Nilai K:</b> Utility degree/tingkat kesesuaian. Semakin tinggi nilainya semakin baik.</li>';
        $html .= '</ul>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
