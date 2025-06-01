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

class RekomendasiMagang extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 3;

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
                Tables\Columns\TextColumn::make('iteration')
                    ->label('#')
                    ->rowIndex()
                    ->alignCenter()
                    ->badge()
                    ->extraHeaderAttributes(['style' => 'width: 60px; min-width: 60px;'])
                    ->extraCellAttributes(['style' => 'width: 60px; min-width: 60px;'])
                    ->color(function ($state) {
                        return match ((int)$state) {
                            1 => 'warning',
                            2 => 'gray',
                            3 => 'danger',
                            default => 'primary',
                        };
                    })
                    ->formatStateUsing(function ($state) {
                        return match ((int)$state) {
                            1 => '🥇 1',
                            2 => '🥈 2',
                            3 => '🥉 3',
                            default => $state,
                        };
                    })
                    ->weight('bold')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('judul_lowongan')
                    ->searchable()
                    ->limit(25)
                    ->label('Lowongan'),
                Tables\Columns\TextColumn::make('perusahaan.nama')
                    ->searchable()
                    ->label('Perusahaan'),
                Tables\Columns\TextColumn::make('jenisMagang.nama_jenis_magang')
                    ->searchable()
                    ->label('Jenis Magang'),
                Tables\Columns\TextColumn::make('daerahMagang.namaLengkapDenganProvinsi')
                    ->limit(15)
                    ->label('Lokasi Magang'),
                Tables\Columns\TextColumn::make('daerahMagang.nama_daerah')
                    ->label('Nama Daerah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan secara default
                Tables\Columns\TextColumn::make('daerahMagang.jenis_daerah')
                    ->label('Jenis Daerah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('waktuMagang.waktu_magang')
                    ->searchable()
                    ->label('Waktu'),
                Tables\Columns\TextColumn::make('insentif.keterangan')
                    ->searchable()
                    ->label('Insentif'),
            ])
            ->striped()
            ->emptyStateHeading('Belum ada lowongan yang tersedia')
            ->emptyStateDescription('Silakan lengkapi preferensi magang Anda untuk mendapatkan rekomendasi lowongan yang sesuai.')
            ->filters([
                Tables\Filters\SelectFilter::make('id_jenis_magang')
                    ->label('Jenis Magang')
                    ->relationship('jenisMagang', 'nama_jenis_magang'),
                Tables\Filters\SelectFilter::make('id_daerah_magang')
                    ->label('Daerah Magang')
                    ->relationship('daerahMagang', 'nama_daerah'),
            ])
            ->actions([
                // Tables\Actions\Action::make('lihat_detail')
                //     ->label('Lihat Detail')
                //     ->url(fn($record) => route('filament.mahasiswa.pages.detail-lowongan', ['record' => $record->id_lowongan]))
                //     ->icon('heroicon-o-eye')
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
            ->get();

        if ($lowonganCollection->isEmpty()) {
            return collect([]);
        }

        // Mendapatkan preferensi jenis magang dan bidang keahlian
        $preferensiJenisMagang = $preferensi->jenisMagang->pluck('id_jenis_magang')->toArray();
        $preferensiBidang = $preferensi->bidangKeahlian->pluck('id_bidang')->toArray();

        // Membuat matriks keputusan (matrix)
        $matrix = [];
        $lowonganIds = [];

        // Menambahkan alternatif optimal sebagai baris pertama (A0)
        $matrix[0] = [
            'daerah' => 1,
            'waktu' => 1,
            'insentif' => 1,
            'jenis' => 1,
            'bidang' => 1,
        ];

        foreach ($lowonganCollection as $index => $lowongan) {
            $rowIndex = $index + 1;
            $lowonganIds[$rowIndex] = $lowongan->id_lowongan;

            // Menghitung kesesuaian daerah
            $daerah = ($lowongan->id_daerah_magang == $preferensi->id_daerah_magang) ? 1 : 0;

            // Menghitung kesesuaian waktu
            $waktu = ($lowongan->id_waktu_magang == $preferensi->id_waktu_magang) ? 1 : 0;

            // Menghitung kesesuaian insentif
            $insentif = ($lowongan->id_insentif == $preferensi->id_insentif) ? 1 : 0;

            // Menghitung kesesuaian jenis magang
            $jenis = in_array($lowongan->id_jenis_magang, $preferensiJenisMagang) ? 1 : 0;

            // Menghitung kesesuaian bidang keahlian
            $lowonganBidang = $lowongan->bidangKeahlian->pluck('id_bidang')->toArray();
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

        // Menghitung jumlah setiap kolom untuk normalisasi
        $colSums = [
            'daerah' => 0,
            'waktu' => 0,
            'insentif' => 0,
            'jenis' => 0,
            'bidang' => 0,
        ];

        foreach ($matrix as $row) {
            $colSums['daerah'] += $row['daerah'];
            $colSums['waktu'] += $row['waktu'];
            $colSums['insentif'] += $row['insentif'];
            $colSums['jenis'] += $row['jenis'];
            $colSums['bidang'] += $row['bidang'];
        }

        // Normalisasi matriks
        $normalizedMatrix = [];
        foreach ($matrix as $i => $row) {
            $normalizedMatrix[$i] = [
                'daerah' => ($colSums['daerah'] > 0) ? $row['daerah'] / $colSums['daerah'] : 0,
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
}
