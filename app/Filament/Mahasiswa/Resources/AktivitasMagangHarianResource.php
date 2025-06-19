<?php

namespace App\Filament\Mahasiswa\Resources;

use App\Filament\Mahasiswa\Resources\AktivitasMagangHarianResource\Pages;
use App\Models\Reference\LogMagangModel;
use App\Models\Reference\PenempatanMagangModel;
use App\Models\Reference\PengajuanMagangModel;
use App\Models\Reference\LowonganMagangModel;
use App\Models\Reference\WaktuMagangModel;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class AktivitasMagangHarianResource extends Resource
{
    protected static ?string $model = LogMagangModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-list';
    protected static ?string $navigationLabel = 'Aktivitas Magang Harian';
    protected static ?string $slug = 'aktivitas-magang-harian';
    protected static ?string $modelLabel = 'Aktivitas Magang';
    protected static ?string $pluralModelLabel = 'Data Aktivitas Magang Harian';
    protected static ?string $navigationSubheading = 'Kelola aktivitas magang harian Anda';
    protected static ?string $navigationGroup = 'Aktivitas & Evaluasi';
    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }
    
    public static function form(Form $form): Form
    {
        $lowonganOptions = [];
        $userId = Auth::id();
        
        // Mendapatkan semua lowongan yang terkait dengan penempatan berlangsung
        $lowongans = LowonganMagangModel::whereHas('pengajuanMagang', function (Builder $query) use ($userId) {
            $query->whereHas('mahasiswa', function (Builder $query) use ($userId) {
                $query->whereHas('user', function (Builder $query) use ($userId) {
                    $query->where('id_user', $userId);
                });
            })->where('status', 'Diterima')
            ->whereHas('penempatan', function (Builder $query) {
                $query->where('status', PenempatanMagangModel::STATUS_BERLANGSUNG);
            });
        })->with(['periode', 'waktuMagang'])->get();
        
        foreach ($lowongans as $lowongan) {
            // Menghitung tanggal berakhir magang berdasarkan periode dan durasi
            if ($lowongan->periode && $lowongan->waktuMagang) {
                // Extract year and semester type from period
                $periodeNama = $lowongan->periode->nama_periode;
                $pattern = '/(\d{4})\/(\d{4})\s+(Ganjil|Genap|Antara)/';
                if (preg_match($pattern, $periodeNama, $matches)) {
                    $tahunAwal = (int)$matches[1];
                    $tahunAkhir = (int)$matches[2];
                    $jenisSemester = $matches[3];
                    
                    // Determine start date based on semester type
                    if ($jenisSemester == 'Ganjil') {
                        $startDate = Carbon::create($tahunAwal, 7, 1);
                    } elseif ($jenisSemester == 'Genap') {
                        $startDate = Carbon::create($tahunAkhir, 1, 1);
                    } else {
                        $startDate = Carbon::create($tahunAwal, 6, 1);
                    }
                    
                    // Extract duration and calculate end date
                    preg_match('/(\d+)/', $lowongan->waktuMagang->waktu_magang, $matches);
                    if (isset($matches[1])) {
                        $bulan = (int)$matches[1];
                        $endDate = $startDate->copy()->addMonths($bulan);
                        
                        // Hanya tambahkan lowongan yang belum berakhir
                        if (Carbon::now()->lte($endDate)) {
                            $lowonganOptions[$lowongan->id_lowongan] = $lowongan->judul_lowongan;
                        }
                    }
                }
            }
        }

        return $form
            ->schema([
                Forms\Components\Select::make('id_lowongan')
                    ->label('Lowongan Magang')
                    ->options($lowonganOptions)
                    ->searchable()
                    ->placeholder('Pilih lowongan magang yang sedang berlangsung')
                    ->helperText('Hanya menampilkan lowongan magang yang sedang berlangsung')
                    ->columnSpanFull()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) use ($userId) {
                        if ($state) {
                            $pengajuan = PengajuanMagangModel::where('id_lowongan', $state)
                                ->whereHas('mahasiswa', function (Builder $query) use ($userId) {
                                    $query->whereHas('user', function (Builder $query) use ($userId) {
                                        $query->where('id_user', $userId);
                                    });
                                })
                                ->where('status', 'Diterima')
                                ->first();
                            
                            if ($pengajuan) {
                                $penempatan = PenempatanMagangModel::where('id_pengajuan', $pengajuan->id_pengajuan)
                                    ->where('status', PenempatanMagangModel::STATUS_BERLANGSUNG)
                                    ->first();
                                    
                                if ($penempatan) {
                                    $set('id_penempatan', $penempatan->id_penempatan);
                                }
                            }
                        }
                    }),
                    
                Forms\Components\Hidden::make('id_penempatan'),
                    
                // Section InfoList Detail Lowongan
                Forms\Components\Section::make('Detail Lowongan')
                    ->description('Informasi tentang lowongan magang yang dipilih')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('placeholder_perusahaan')
                                            ->label('Perusahaan')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                $lowongan = LowonganMagangModel::with('perusahaan')->find($idLowongan);
                                                return $lowongan && $lowongan->perusahaan 
                                                    ? $lowongan->perusahaan->nama ?? 'N/A'
                                                    : 'N/A';
                                            })
                                            ->hintIcon('heroicon-o-building-office'),
                                    ]),
                                
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('placeholder_bidang')
                                            ->label('Bidang Keahlian')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                $lowongan = LowonganMagangModel::with('bidangKeahlian')->find($idLowongan);
                                                
                                                if (!$lowongan || $lowongan->bidangKeahlian->isEmpty()) {
                                                    return 'N/A';
                                                }
                                                
                                                // Mengumpulkan semua nama bidang keahlian
                                                $bidangNames = $lowongan->bidangKeahlian->pluck('nama_bidang_keahlian')->toArray();
                                                
                                                // Menggabungkan dengan koma
                                                return implode(', ', $bidangNames);
                                            })
                                            ->hintIcon('heroicon-o-briefcase'),
                                    ]),
                                    
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('placeholder_durasi')
                                            ->label('Durasi Magang')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                $lowongan = LowonganMagangModel::with('waktuMagang')->find($idLowongan);
                                                return $lowongan && $lowongan->waktuMagang 
                                                    ? $lowongan->waktuMagang->waktu_magang ?? 'N/A'
                                                    : 'N/A';
                                            })
                                            ->hintIcon('heroicon-o-clock'),
                                    ]),
                                    
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('placeholder_lokasi_kantor')
                                            ->label('Alamat Perusahaan Pusat')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                $lowongan = LowonganMagangModel::with('perusahaan')->find($idLowongan);
                                                return $lowongan && $lowongan->perusahaan 
                                                    ? $lowongan->perusahaan->alamat ?? 'N/A'
                                                    : 'N/A';
                                            })
                                            ->hintIcon('heroicon-o-map-pin'),
                                    ]),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('placeholder_lokasi_magang')
                                            ->label('Lokasi Tempat Magang')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                $lowongan = LowonganMagangModel::with(['daerahMagang.provinsi'])->find($idLowongan);
                                                
                                                if (!$lowongan || !$lowongan->daerahMagang) {
                                                    return 'N/A';
                                                }
                                                
                                                // menggunakan accessor yang ada pada model
                                                if (method_exists($lowongan->daerahMagang, 'namaLengkapDenganProvinsi')) {
                                                    return $lowongan->daerahMagang->namaLengkapDenganProvinsi;
                                                }
                                                
                                                // fallback jika accessor tidak tersedia
                                                $daerah = $lowongan->daerahMagang;
                                                $provinsi = $daerah->provinsi ? $daerah->provinsi->nama_provinsi : 'N/A';
                                                return "{$daerah->nama_daerah}, {$daerah->jenis_daerah}, {$provinsi}";
                                            })
                                            ->hintIcon('heroicon-o-map-pin'),
                                    ]),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('placeholder_periode_magang')
                                            ->label('Periode Magang')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                $lowongan = LowonganMagangModel::with(['periode'])->find($idLowongan);

                                                if (!$lowongan || !$lowongan->periode) {
                                                    return 'N/A';
                                                }

                                                return $lowongan->periode->nama_periode ?? 'N/A';
                                            })
                                            ->hintIcon('heroicon-o-calendar'),
                                    ]),
                            ])
                            ->columns(2),
                            
                        Forms\Components\Placeholder::make('placeholder_deskripsi')
                            ->label('Deskripsi Lowongan')
                            ->content(function (Get $get) {
                                $idLowongan = $get('id_lowongan');
                                if (!$idLowongan) return 'Tidak ada deskripsi';
                                
                                static $lowongan = null;
                                if ($lowongan === null) {
                                    $lowongan = LowonganMagangModel::with(['periode'])->find($idLowongan);
                                }
                                return $lowongan ? $lowongan->deskripsi_lowongan ?? 'Tidak ada deskripsi' : 'Tidak ada deskripsi';
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->visible(fn (Get $get): bool => (bool) $get('id_lowongan'))
                    ->extraAttributes([
                        'class' => 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg',
                    ]),

                // Section InfoList Detail Magang
                Forms\Components\Section::make('Status Pelaksanaan Magang')
                    ->description('Ringkasan waktu pelaksanaan dan status durasi magang berdasarkan lowongan terpilih')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                // Masa Magang
                                Forms\Components\Placeholder::make('placeholder_estimasi_magang')
                                    ->label('Masa Magang')
                                    ->content(function (Get $get) {
                                        $idLowongan = $get('id_lowongan');
                                        if (!$idLowongan) return 'N/A';
                                        
                                        // Get the lowongan with its related periode and waktu_magang
                                        $lowongan = LowonganMagangModel::with(['periode', 'waktuMagang'])->find($idLowongan);
                                        if (!$lowongan || !$lowongan->periode || !$lowongan->waktuMagang) {
                                            return 'N/A';
                                        }
                                        
                                        // Extract year and semester type from the period name (e.g., "2024/2025 Ganjil")
                                        $periodeNama = $lowongan->periode->nama_periode;
                                        $pattern = '/(\d{4})\/(\d{4})\s+(Ganjil|Genap|Antara)/';
                                        if (!preg_match($pattern, $periodeNama, $matches)) {
                                            return 'N/A';
                                        }
                                        
                                        $tahunAwal = (int)$matches[1];
                                        $tahunAkhir = (int)$matches[2];
                                        $jenisSemester = $matches[3];
                                        
                                        // Determine start date based on semester type
                                        if ($jenisSemester == 'Ganjil') {
                                            // Odd semester starts in July of the first year
                                            $startDate = Carbon::create($tahunAwal, 7, 1);
                                        } elseif ($jenisSemester == 'Genap') {
                                            // Even semester starts in January of the second year
                                            $startDate = Carbon::create($tahunAkhir, 1, 1);
                                        } else {
                                            // Antara (short semester) could be in between, using June as default
                                            $startDate = Carbon::create($tahunAwal, 6, 1);
                                        }
                                        
                                        // Extract duration from waktu_magang (e.g., "6 Bulan")
                                        preg_match('/(\d+)/', $lowongan->waktuMagang->waktu_magang, $matches);
                                        if (!isset($matches[1])) {
                                            return 'N/A';
                                        }
                                        
                                        $bulan = (int)$matches[1];
                                        $endDate = $startDate->copy()->addMonths($bulan);
                                        
                                        $startFormatted = $startDate->format('d M Y');
                                        $endFormatted = $endDate->format('d M Y');
                                        
                                        return "Mulai: {$startFormatted} - Selesai: {$endFormatted}";
                                    })
                                    ->hintIcon('heroicon-o-calendar-date-range'),
                                
                                // Status Durasi (Group Baru)
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('placeholder_sisa_waktu')
                                            ->label('Status Durasi Magang')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                // Get the lowongan with its related periode and waktu_magang
                                                $lowongan = LowonganMagangModel::with(['periode', 'waktuMagang'])->find($idLowongan);
                                                if (!$lowongan || !$lowongan->periode || !$lowongan->waktuMagang) {
                                                    return 'N/A';
                                                }
                                                
                                                // Extract year and semester type from period
                                                $periodeNama = $lowongan->periode->nama_periode;
                                                $pattern = '/(\d{4})\/(\d{4})\s+(Ganjil|Genap|Antara)/';
                                                if (!preg_match($pattern, $periodeNama, $matches)) {
                                                    return 'N/A';
                                                }
                                                
                                                $tahunAwal = (int)$matches[1];
                                                $tahunAkhir = (int)$matches[2];
                                                $jenisSemester = $matches[3];
                                                
                                                // Determine start date based on semester type
                                                if ($jenisSemester == 'Ganjil') {
                                                    $startDate = Carbon::create($tahunAwal, 7, 1);
                                                } elseif ($jenisSemester == 'Genap') {
                                                    $startDate = Carbon::create($tahunAkhir, 1, 1);
                                                } else {
                                                    $startDate = Carbon::create($tahunAwal, 6, 1);
                                                }
                                                
                                                // Extract duration and calculate end date
                                                preg_match('/(\d+)/', $lowongan->waktuMagang->waktu_magang, $matches);
                                                if (!isset($matches[1])) {
                                                    return 'N/A';
                                                }
                                                
                                                $bulan = (int)$matches[1];
                                                $endDate = $startDate->copy()->addMonths($bulan);
                                                
                                                // Compare with current date
                                                $now = Carbon::now();
                                                
                                                // Jika magang sudah berakhir
                                                if ($now > $endDate) {
                                                    return 'Magang telah berakhir';
                                                }
                                                
                                                // Jika belum dimulai
                                                if ($now < $startDate) {
                                                    // Hitung waktu tunggu hingga mulai magang
                                                    $diffInMonths = $now->diffInMonths($startDate);
                                                    $diffInDays = $now->copy()->addMonths($diffInMonths)->diffInDays($startDate);
                                                    
                                                    if ($diffInMonths > 0 && $diffInDays > 0) {
                                                        return "Magang dimulai dalam $diffInMonths bulan $diffInDays hari lagi";
                                                    } elseif ($diffInMonths > 0) {
                                                        return "Magang dimulai dalam $diffInMonths bulan lagi";
                                                    } elseif ($diffInDays > 6) {
                                                        return "Magang dimulai dalam $diffInDays hari lagi";
                                                    } elseif ($diffInDays > 0) {
                                                        return "Magang dimulai dalam $diffInDays hari lagi! Persiapkan diri Anda";
                                                    } else {
                                                        return "Magang dimulai hari ini! Semoga sukses";
                                                    }
                                                }
                                                
                                                // Jika magang sedang berlangsung
                                                $diffInMonths = $now->diffInMonths($endDate);
                                                $diffInDays = $now->copy()->addMonths($diffInMonths)->diffInDays($endDate);
                                                
                                                if ($diffInMonths > 0 && $diffInDays > 0) {
                                                    return "Sisa waktu magang: $diffInMonths bulan $diffInDays hari lagi";
                                                } elseif ($diffInMonths > 0) {
                                                    return "Sisa waktu magang: $diffInMonths bulan lagi";
                                                } elseif ($diffInDays > 1) {
                                                    return "Sisa waktu magang: $diffInDays hari lagi";
                                                } else if ($diffInDays == 1) {
                                                    return "Hari terakhir magang besok";
                                                } else {
                                                    return "Hari terakhir magang hari ini";
                                                }
                                            })
                                            ->hintIcon('heroicon-o-clock'),
                                    ])
                            ])
                            ->columns(2),
                    ])
                    ->visible(fn (Get $get): bool => (bool) $get('id_lowongan'))
                    ->extraAttributes([
                        'class' => 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg',
                    ]),

                Forms\Components\Section::make('Informasi Dosen Pembimbing')
                    ->description('Detail dosen pembimbing untuk magang ini')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('nama_dosen')
                                            ->label('Nama Dosen Pembimbing')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                // Dapatkan pengajuan dan penempatan terkait
                                                $pengajuan = PengajuanMagangModel::where('id_lowongan', $idLowongan)
                                                    ->whereHas('mahasiswa', function (Builder $query) {
                                                        $query->whereHas('user', function (Builder $query) {
                                                            $query->where('id_user', Auth::id());
                                                        });
                                                    })
                                                    ->where('status', 'Diterima')
                                                    ->first();
                                                
                                                if (!$pengajuan) return 'N/A';
                                                
                                                $penempatan = PenempatanMagangModel::where('id_pengajuan', $pengajuan->id_pengajuan)->first();
                                                
                                                if (!$penempatan || !$penempatan->dosenPembimbing()->exists()) {
                                                    return 'Belum ditentukan';
                                                }
                                                
                                                return $penempatan->dosenPembimbing()->first()->user->nama ?? 'N/A';
                                            })
                                            ->hintIcon('heroicon-o-user'),
                                    ]),
                                    
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('nip_dosen')
                                            ->label('NIP Dosen')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                // Dapatkan pengajuan dan penempatan terkait
                                                $pengajuan = PengajuanMagangModel::where('id_lowongan', $idLowongan)
                                                    ->whereHas('mahasiswa', function (Builder $query) {
                                                        $query->whereHas('user', function (Builder $query) {
                                                            $query->where('id_user', Auth::id());
                                                        });
                                                    })
                                                    ->where('status', 'Diterima')
                                                    ->first();
                                                
                                                if (!$pengajuan) return 'N/A';
                                                
                                                $penempatan = PenempatanMagangModel::where('id_pengajuan', $pengajuan->id_pengajuan)->first();
                                                
                                                if (!$penempatan || !$penempatan->dosenPembimbing()->exists()) {
                                                    return 'Belum ditentukan';
                                                }
                                                
                                                return $penempatan->dosenPembimbing()->first()->nip ?? 'N/A';
                                            })
                                            ->hintIcon('heroicon-o-identification'),
                                    ]),
                                    
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('telepon_dosen')
                                            ->label('Nomor Telepon Dosen')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                // Dapatkan pengajuan dan penempatan terkait
                                                $pengajuan = PengajuanMagangModel::where('id_lowongan', $idLowongan)
                                                    ->whereHas('mahasiswa', function (Builder $query) {
                                                        $query->whereHas('user', function (Builder $query) {
                                                            $query->where('id_user', Auth::id());
                                                        });
                                                    })
                                                    ->where('status', 'Diterima')
                                                    ->first();
                                                
                                                if (!$pengajuan) return 'N/A';
                                                
                                                $penempatan = PenempatanMagangModel::where('id_pengajuan', $pengajuan->id_pengajuan)->first();
                                                
                                                if (!$penempatan || !$penempatan->dosenPembimbing()->exists()) {
                                                    return 'Belum ditentukan';
                                                }
                                                
                                                return $penempatan->dosenPembimbing()->first()->user->no_telepon ?? 'N/A';
                                            })
                                            ->hintIcon('heroicon-o-phone'),
                                    ]),
                                
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('bidang_keahlian_dosen')
                                            ->label('Bidang Keahlian Dosen')
                                            ->content(function (Get $get) {
                                                $idLowongan = $get('id_lowongan');
                                                if (!$idLowongan) return 'N/A';
                                                
                                                // Dapatkan pengajuan dan penempatan terkait
                                                $pengajuan = PengajuanMagangModel::where('id_lowongan', $idLowongan)
                                                    ->whereHas('mahasiswa', function (Builder $query) {
                                                        $query->whereHas('user', function (Builder $query) {
                                                            $query->where('id_user', Auth::id());
                                                        });
                                                    })
                                                    ->where('status', 'Diterima')
                                                    ->first();
                                                
                                                if (!$pengajuan) return 'N/A';
                                                
                                                $penempatan = PenempatanMagangModel::where('id_pengajuan', $pengajuan->id_pengajuan)->first();
                                                
                                                if (!$penempatan || !$penempatan->dosenPembimbing()->exists()) {
                                                    return 'Belum ditentukan';
                                                }
                                                
                                                $dosen = $penempatan->dosenPembimbing()->first();
                                                $bidangKeahlian = $dosen->bidangKeahlian;
                                                
                                                if ($bidangKeahlian->isEmpty()) {
                                                    return 'Tidak ada bidang keahlian terdaftar';
                                                }
                                                
                                                return $bidangKeahlian->pluck('nama_bidang_keahlian')->implode(', ');
                                            })
                                            ->hintIcon('heroicon-o-academic-cap'),
                                    ]),
                            ])
                            ->columns(2)
                    ])
                    ->collapsible()
                    ->collapsed(true)
                    ->visible(fn (Get $get): bool => (bool) $get('id_lowongan'))
                    ->extraAttributes([
                        'class' => 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg',
                    ]),
                    
                Forms\Components\DatePicker::make('tanggal_log')
                    ->native(false)
                    ->label('Tanggal Log Aktivitas')
                    ->required()
                    ->maxDate(now())
                    ->default(now())
                    ->hint('Tanggal pencatatan aktivitas, default: hari ini.')
                    ->displayFormat('D, d M Y') // Format Flatpickr
                    ->format('Y-m-d') // format nilai yang disimpan
                    ->locale('id') // locale bahasa Indonesia
                    ->readOnly(),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->required()
                    ->options([
                        'masuk' => 'Masuk',
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                    ]),
                
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(7)
                    ->placeholder('Masukkan keterangan aktivitas magang')
                    ->helperText('Keterangan ini akan terlihat oleh pembimbing magang Anda.')
                    ->maxLength(300)
                    ->required(),
                
                Forms\Components\FileUpload::make('file_bukti')
                    ->label('File Bukti')
                    ->disk('cloudinary')
                    ->directory('bukti-magang')
                    ->visibility('public')
                    ->required()
                    ->image()
                    ->imagePreviewHeight('250')
                    ->loadingIndicatorPosition('left')
                    ->panelAspectRatio('2:1')
                    ->panelLayout('integrated')
                    ->removeUploadedFileButtonPosition('right')
                    ->uploadButtonPosition('left')
                    ->uploadProgressIndicatorPosition('left')
                    ->maxSize(5 * 1024) // 10MB
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => 
                            'bukti-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension()
                    )
                    ->helperText('Unggah bukti aktivitas (JPG, PNG max 5MB)'),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Mendapatkan ID user yang sedang login
        $userId = Auth::id();

        // Mendapatkan penempatan magang mahasiswa
        $penempatanMagang = PenempatanMagangModel::whereHas('mahasiswa', function (Builder $query) use ($userId) {
            $query->whereHas('user', function (Builder $query) use ($userId) {
                $query->where('id_user', $userId);
            });
        })->get();

        // ID pengajuan dari penempatan
        $idPengajuan = $penempatanMagang->pluck('id_pengajuan')->toArray();

        // Mendapatkan lowongan dari pengajuan
        $lowonganOptions = LowonganMagangModel::whereHas('pengajuanMagang', function ($query) use ($idPengajuan) {
            $query->whereIn('id_pengajuan', $idPengajuan);
        })->pluck('judul_lowongan', 'id_lowongan')->toArray();

        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->defaultPaginationPageOption(12)
            ->defaultSort('created_at', 'desc') 
            ->columns([
                // Tables\Columns\ImageColumn::make('file_bukti')
                //     ->disk('cloudinary')
                //     ->height(200)
                //     ->extraImgAttributes(['class' => 'w-full object-cover rounded-t-lg'])
                //     ->alignCenter(),
                Tables\Columns\ViewColumn::make('file_bukti')
                    ->label('Bukti')
                    ->view('filament.components.bukti-indicator')
                    ->alignCenter(),
                
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\TextColumn::make('tanggal_log')
                        ->date('d F Y')
                        ->weight('bold')
                        ->size('lg'),
                    
                    Tables\Columns\TextColumn::make('status')
                        ->badge()
                        ->colors([
                            'success' => 'masuk',
                            'warning' => 'izin',
                            'danger' => 'sakit',
                            'info' => 'cuti',
                        ])
                        ->icon(fn ($record) => match($record->status) {
                            'masuk' => 'heroicon-o-check-circle',
                            'izin' => 'heroicon-o-exclamation',
                            'sakit' => 'heroicon-o-x-circle',
                            'cuti' => 'heroicon-o-briefcase',
                            default => 'heroicon-o-information-circle',
                        }),

                    Tables\Columns\TextColumn::make('keterangan')
                        ->label('Deskripsi')
                        ->icon('heroicon-o-document-text')
                        ->limit(30)
                        ->tooltip(fn ($state, $record) => strlen($state) > 60 ? $state : null),

                    Tables\Columns\TextColumn::make('feedback_progres')
                        ->label('Feedback')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('gray')
                        ->placeholder('Belum ada feedback')
                        ->limit(40)
                        ->tooltip(fn ($state) => strlen($state) > 40 ? $state : null)


                ])
            ])
            ->filters([
                // Filter builder kompleks untuk lowongan magang
                Tables\Filters\Filter::make('filter_lowongan_magang')
                    ->form([
                        Forms\Components\Section::make('Filter Lowongan Magang')
                            ->description('Pilih lowongan magang untuk melihat aktivitas')
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Select::make('id_lowongan')
                                            ->label('Lowongan Magang')
                                            ->options($lowonganOptions)
                                            ->searchable()
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function($state, callable $set, callable $get) {
                                                if ($state) {
                                                    // Mendapatkan data lowongan dengan periode dan waktu magang
                                                    $lowongan = LowonganMagangModel::with(['periode', 'waktuMagang'])->find($state);
                                                    
                                                    if ($lowongan && $lowongan->periode && $lowongan->waktuMagang) {
                                                        // Extract year and semester type from period name
                                                        $periodeNama = $lowongan->periode->nama_periode;
                                                        $pattern = '/(\d{4})\/(\d{4})\s+(Ganjil|Genap|Antara)/';
                                                        
                                                        if (preg_match($pattern, $periodeNama, $matches)) {
                                                            $tahunAwal = (int)$matches[1];
                                                            $tahunAkhir = (int)$matches[2];
                                                            $jenisSemester = $matches[3];
                                                            
                                                            // Determine start date based on semester type
                                                            if ($jenisSemester == 'Ganjil') {
                                                                // Odd semester starts in July of the first year
                                                                $startDate = Carbon::create($tahunAwal, 7, 1);
                                                            } elseif ($jenisSemester == 'Genap') {
                                                                // Even semester starts in January of the second year
                                                                $startDate = Carbon::create($tahunAkhir, 1, 1);
                                                            } else {
                                                                // Antara (short semester) could be in between, using June as default
                                                                $startDate = Carbon::create($tahunAwal, 6, 1);
                                                            }
                                                            
                                                            // Set dari_tanggal berdasarkan periode
                                                            $set('dari_tanggal', $startDate->format('Y-m-d'));
                                                            
                                                            // Extract duration from waktu_magang (e.g., "6 Bulan")
                                                            preg_match('/(\d+)/', $lowongan->waktuMagang->waktu_magang, $matches);
                                                            
                                                            if (isset($matches[1])) {
                                                                $bulan = (int)$matches[1];
                                                                $endDate = $startDate->copy()->addMonths($bulan);
                                                                
                                                                // Set sampai_tanggal berdasarkan dari_tanggal + durasi
                                                                $set('sampai_tanggal', $endDate->format('Y-m-d'));
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    $set('dari_tanggal', null);
                                                    $set('sampai_tanggal', null);
                                                }
                                            }),

                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'masuk' => 'Masuk',
                                                'izin' => 'Izin',
                                                'sakit' => 'Sakit',
                                                'cuti' => 'Cuti',
                                            ])
                                            ->placeholder('Semua status')
                                            ->live(),
                                    ])->columns(2),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('dari_tanggal')
                                            ->native(false)
                                            ->label('Dari Tanggal')
                                            ->hint('Tanggal anda mulai magang')
                                            ->displayFormat('D, d M Y') // Format Flatpickr
                                            ->format('Y-m-d') // format nilai yang disimpan - penting untuk konsistensi
                                            ->locale('id')
                                            ->disabled(fn (Get $get): bool => $get('id_lowongan') === null)
                                            ->dehydrated(true) // Pastikan nilai selalu dikirim
                                            ->afterStateHydrated(function ($state, Forms\Components\DatePicker $component) {
                                                // Normalisasi format tanggal saat loading
                                                if ($state && !is_string($state)) {
                                                    $component->state(Carbon::parse($state)->format('Y-m-d'));
                                                }
                                            }),
                                            
                                        Forms\Components\DatePicker::make('sampai_tanggal')
                                            ->native(false)
                                            ->label('Sampai Tanggal')
                                            ->hint('Tanggal anda selesai magang')
                                            ->displayFormat('D, d M Y')
                                            ->format('Y-m-d') // format nilai yang disimpan - penting untuk konsistensi
                                            ->locale('id')
                                            ->disabled(fn (Get $get): bool => $get('id_lowongan') === null)
                                            ->dehydrated(true) // Pastikan nilai selalu dikirim
                                            ->afterStateHydrated(function ($state, Forms\Components\DatePicker $component) {
                                                // Normalisasi format tanggal saat loading
                                                if ($state && !is_string($state)) {
                                                    $component->state(Carbon::parse($state)->format('Y-m-d'));
                                                }
                                            }),
                                    ])->columns(2),
                            ]),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if (isset($data['id_lowongan'])) {
                            $lowongan = LowonganMagangModel::find($data['id_lowongan']);
                            if ($lowongan) {
                                $indicators[] = Indicator::make('Lowongan: ' . $lowongan->judul_lowongan)
                                    ->removable(false);
                            }
                        }
                        
                        if (isset($data['status'])) {
                            $statusLabels = [
                                'masuk' => 'Masuk',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'cuti' => 'Cuti',
                            ];
                            
                            $indicators[] = Indicator::make('Status: ' . $statusLabels[$data['status']])
                                ->removable(true)
                                ->removeField('status');
                        }
                        
                        if (isset($data['dari_tanggal'])) {
                            $indicators[] = Indicator::make('Dari: ' . Carbon::parse($data['dari_tanggal'])->format('d F Y'))
                                ->removable(true)
                                ->removeField('dari_tanggal');
                        }
                        
                        if (isset($data['sampai_tanggal'])) {
                            $indicators[] = Indicator::make('Sampai: ' . Carbon::parse($data['sampai_tanggal'])->format('d F Y'))
                                ->removable(true)
                                ->removeField('sampai_tanggal');
                        }
                        
                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        if (!isset($data['id_lowongan'])) {
                            // Jika lowongan belum dipilih, tampilkan data kosong
                            return $query->whereRaw('1 = 0');
                        }
                        
                        // Cari penempatan yang terkait dengan lowongan yang dipilih
                        $idLowongan = $data['id_lowongan'];
                        $pengajuan = PengajuanMagangModel::where('id_lowongan', $idLowongan)
                            ->pluck('id_pengajuan')
                            ->toArray();
                        
                        $penempatan = PenempatanMagangModel::whereIn('id_pengajuan', $pengajuan)
                            ->pluck('id_penempatan')
                            ->toArray();
                        
                        // Filter berdasarkan penempatan, status, dan tanggal
                        $query = $query->whereIn('id_penempatan', $penempatan);
                        
                        if (isset($data['status'])) {
                            $query->where('status', $data['status']);
                        }
                        
                        // PERBAIKAN FILTER TANGGAL
                        if (isset($data['dari_tanggal']) && !empty($data['dari_tanggal'])) {
                            // Normalisasi format tanggal - ekstrak tanggal saja dari datetime
                            try {
                                $dari = Carbon::parse($data['dari_tanggal'])->startOfDay();
                                $query->where('tanggal_log', '>=', $dari->format('Y-m-d'));
                            } catch (\Exception $e) {
                                // Log error jika format tanggal tidak valid
                                Log::error('Format tanggal dari_tanggal tidak valid', [
                                    'value' => $data['dari_tanggal'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        if (isset($data['sampai_tanggal']) && !empty($data['sampai_tanggal'])) {
                            // Normalisasi format tanggal - ekstrak tanggal saja dari datetime
                            try {
                                $sampai = Carbon::parse($data['sampai_tanggal'])->endOfDay();
                                $query->where('tanggal_log', '<=', $sampai->format('Y-m-d'));
                            } catch (\Exception $e) {
                                // Log error jika format tanggal tidak valid
                                Log::error('Format tanggal sampai_tanggal tidak valid', [
                                    'value' => $data['sampai_tanggal'],
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        return $query;
                    })
                    ->default([
                        'id_lowongan' => null
                    ]),
            ], layout: FiltersLayout::AboveContent) 
            ->filtersFormWidth('3xl') // penting untuk style filter
            ->filtersFormColumns(1) // penting untuk style filter 
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat Detail'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus Aktivitas'),
            ], Tables\Enums\ActionsPosition::AfterContent)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->recordUrl(null)
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading('Belum Ada Aktivitas Magang yang Ditampilkan')
            ->emptyStateDescription('Pilih lowongan magang menggunakan filter di atas untuk melihat aktivitas, atau buat aktivitas baru dengan menekan tombol di pojok kanan atas.');
            // ->emptyStateIcon(function () {
            //     $request = request();
            //     $filterApplied = $request->filled('tableFilters.filter_lowongan_magang.id_lowongan');
                
            //     return $filterApplied 
            //         ? 'heroicon-o-document' 
            //         : 'heroicon-o-funnel';
            // })
            // ->emptyStateHeading(function () {
            //     $request = request();
            //     $filterApplied = $request->filled('tableFilters.filter_lowongan_magang.id_lowongan');
                
            //     return $filterApplied 
            //         ? 'Belum ada aktivitas magang' 
            //         : 'Pilih Filter Lowongan Magang';
            // })
            // ->emptyStateDescription(function () {
            //     $request = request();
            //     $filterApplied = $request->filled('tableFilters.filter_lowongan_magang.id_lowongan');
                
            //     return $filterApplied 
            //         ? 'Belum ada data aktivitas magang untuk lowongan yang dipilih.' 
            //         : 'Gunakan filter lowongan magang di atas untuk melihat aktivitas magang Anda.';
            // });
            



    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Detail Aktivitas Magang')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\Group::make([
                                    Components\TextEntry::make('tanggal_log')
                                        ->label('Tanggal')
                                        ->date('d F Y'),
                                    
                                    Components\TextEntry::make('status')
                                        ->badge()
                                        ->formatStateUsing(fn (string $state): string => ucfirst($state))
                                        ->color(fn (string $state): string => match ($state) {
                                            'masuk' => 'success',
                                            'izin' => 'warning',
                                            'sakit' => 'danger',
                                            'cuti' => 'info',
                                            default => 'gray',
                                        }),
                                ]),
                                
                                Components\Group::make([
                                    Components\TextEntry::make('penempatan.pengajuan.lowongan.judul_lowongan')
                                        ->label('Lowongan')
                                        ->placeholder('Tidak ada informasi lowongan'),
                                        
                                    Components\TextEntry::make('penempatan.pengajuan.lowongan.perusahaan.nama')
                                        ->label('Perusahaan')
                                        ->placeholder('Tidak ada informasi perusahaan'),
                                ]),
                            ]),
                            
                        Components\Section::make('Keterangan')
                            ->schema([
                                Components\TextEntry::make('keterangan')
                                    ->prose()
                                    ->markdown()
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                        
                        Components\Section::make('Feedback Pembimbing')
                            ->schema([
                                Components\TextEntry::make('feedback_progres')
                                    ->label('Feedback')
                                    ->placeholder('Belum ada feedback')
                                    ->prose()
                                    ->markdown()
                                    ->columnSpanFull(),
                            ])
                            ->collapsible(),
                            
                        Components\Section::make('Bukti Aktivitas')
                            ->schema([
                                Components\ImageEntry::make('file_bukti')
                                    ->disk('cloudinary')
                                    ->height(300)
                                    ->extraImgAttributes(['class' => 'rounded-lg object-contain shadow-sm']),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAktivitasMagangHarians::route('/'),
            'create' => Pages\CreateAktivitasMagangHarian::route('/create'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $userId = Auth::id();
        
        return parent::getEloquentQuery()
            ->whereHas('penempatan', function (Builder $query) use ($userId) {
                $query->whereHas('mahasiswa', function (Builder $query) use ($userId) {
                    $query->whereHas('user', function (Builder $query) use ($userId) {
                        $query->where('id_user', $userId);
                    });
                });
            })
            ->with(['penempatan.pengajuan.lowongan.perusahaan'])
            ->orderBy('tanggal_log', 'desc');
    }
}
