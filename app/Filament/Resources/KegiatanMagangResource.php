<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanMagangResource\Pages;
use App\Filament\Resources\KegiatanMagangResource\RelationManagers;
use App\Models\KegiatanMagang;
use App\Models\Reference\PengajuanMagangModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\DB;

class KegiatanMagangResource extends Resource
{
    protected static ?string $model = PengajuanMagangModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-document-check';
    protected static ?string $navigationLabel = 'Pengajuan Magang';
    protected static ?string $slug = 'kegiatan-magang';
    protected static ?string $modelLabel = 'Pengajuan Magang';
    protected static ?string $pluralModelLabel = 'Data Pengajuan Magang';
    protected static ?string $navigationGroup = 'Administrasi Magang';

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Mahasiswa')
                    ->schema([
                        Infolists\Components\TextEntry::make('mahasiswa.user.nama')
                            ->label('Nama Mahasiswa'),

                        Infolists\Components\TextEntry::make('mahasiswa.nim')
                            ->label('NIM'),

                        Infolists\Components\TextEntry::make('mahasiswa.prodi.nama_prodi')
                            ->label('Program Studi'),

                        Infolists\Components\TextEntry::make('mahasiswa.ipk')
                            ->label('IPK Mahasiswa'),

                        Infolists\Components\TextEntry::make('mahasiswa.semester')
                            ->label('Semester Mahasiswa'),

                        Infolists\Components\TextEntry::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->date('Y-m-d'),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->label('Status Pengajuan')
                            ->color(fn(string $state): string => match ($state) {
                                'Diajukan' => 'warning',
                                'Diterima' => 'success',
                                'Ditolak' => 'danger',
                                default => 'gray',
                            }),

                        Infolists\Components\Section::make('Preferensi Mahasiswa')
                            ->schema([
                                Infolists\Components\TextEntry::make('mahasiswa.preferensi.daerahMagang.namaLengkapDenganProvinsi')
                                    ->label('Daerah Magang')
                                    ->default('Semua')
                                    ->placeholder('Semua'),

                                Infolists\Components\TextEntry::make('jenisMagang')
                                    ->label('Jenis Magang')
                                    ->default('Semua')
                                    ->placeholder('Semua')
                                    ->getStateUsing(function ($record) {
                                        try {
                                            if (!$record->mahasiswa->preferensi) {
                                                return 'Semua';
                                            }

                                            $preferensi = $record->mahasiswa->preferensi;

                                            $jenisMagangs = DB::table('m_jenis_magang')
                                                ->join('r_preferensi_jenis_magang', 'm_jenis_magang.id_jenis_magang', '=', 'r_preferensi_jenis_magang.id_jenis_magang')
                                                ->where('r_preferensi_jenis_magang.id_preferensi', $preferensi->id_preferensi)
                                                ->pluck('m_jenis_magang.nama_jenis_magang')
                                                ->toArray();

                                            if (empty($jenisMagangs)) {
                                                return 'Semua';
                                            }

                                            return implode(', ', $jenisMagangs);
                                        } catch (\Exception $e) {
                                            return 'Semua: ' . $e->getMessage();
                                        }
                                    }),

                                Infolists\Components\TextEntry::make('bidangMahasiswa')
                                    ->label('Bidang Keahlian')
                                    ->default('Semua')
                                    ->placeholder('Semua')
                                    ->getStateUsing(function ($record) {
                                        try {
                                            if (!$record->mahasiswa->preferensi) {
                                                return 'Semua';
                                            }

                                            $preferensi = $record->mahasiswa->preferensi;
                                            $bidangKeahlians = DB::table('m_bidang_keahlian')
                                                ->join('r_preferensi_bidang', 'm_bidang_keahlian.id_bidang', '=', 'r_preferensi_bidang.id_bidang')
                                                ->where('r_preferensi_bidang.id_preferensi', $preferensi->id_preferensi)
                                                ->pluck('m_bidang_keahlian.nama_bidang_keahlian')
                                                ->toArray();

                                            if (empty($bidangKeahlians)) {
                                                return 'Semua';
                                            }

                                            return implode(', ', $bidangKeahlians);
                                        } catch (\Exception $e) {
                                            return 'Semua: ' . $e->getMessage();
                                        }
                                    }),

                                Infolists\Components\TextEntry::make('mahasiswa.preferensi.insentif.keterangan')
                                    ->label('Insentif')
                                    ->default('Semua')
                                    ->placeholder('Semua'),

                                Infolists\Components\TextEntry::make('mahasiswa.preferensi.waktuMagang.waktu_magang')
                                    ->label('Waktu Magang')
                                    ->default('Semua')
                                    ->placeholder('Semua'),

                            ])->columns(2)
                            ->collapsible(),

                    ])->columns(3),

                Infolists\Components\Section::make('Informasi Lowongan')
                    ->schema([
                        Infolists\Components\TextEntry::make('lowongan.judul_lowongan')
                            ->label('Judul Lowongan'),

                        Infolists\Components\TextEntry::make('lowongan.perusahaan.nama')
                            ->label('Perusahaan'),

                        Infolists\Components\TextEntry::make('lowongan.bidangKeahlian.nama_bidang_keahlian')
                            ->label('Bidang Keahlian'),

                        Infolists\Components\TextEntry::make('lowongan.jenisMagang.nama_jenis_magang')
                            ->label('Jenis Magang'),

                        Infolists\Components\TextEntry::make('lowongan.daerahMagang.namaLengkapDenganProvinsi')
                            ->label('Daerah Magang'),

                        Infolists\Components\TextEntry::make('lowongan.periode.nama_periode')
                            ->label('Periode Magang'),

                        Infolists\Components\TextEntry::make('lowongan.waktuMagang.waktu_magang')
                            ->label('Waktu Magang'),

                        Infolists\Components\TextEntry::make('lowongan.batas_akhir_lamaran')
                            ->label('Batas Akhir Lamaran')
                            ->date('Y-m-d'),

                        Infolists\Components\TextEntry::make('lowongan.status')
                            ->badge()
                            ->label('Status Lowongan')
                            ->color(fn(string $state): string => match ($state) {
                                'Aktif' => 'success',
                                'Selesai' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(3),

            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Status Pengajuan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Pengajuan')
                            ->default('Diajukan')
                            ->options([
                                'Diajukan' => 'Diajukan',
                                'Diterima' => 'Diterima',
                                'Ditolak' => 'Ditolak',
                            ])
                            ->live()
                            ->afterStateUpdated(function (string $state, callable $set) {
                                if ($state === 'Diterima') {
                                    $set('tanggal_diterima', now()->format('Y-m-d'));
                                }
                            })
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_diterima')
                            ->label('Tanggal Diterima')
                            ->default(now()->format('Y-m-d'))
                            ->displayFormat('Y-m-d')
                            ->visible(fn(Forms\Get $get) => $get('status') === 'Diterima')
                            ->disabled() // Make it read-only
                            ->dehydrated(fn(Forms\Get $get) => $get('status') === 'Diterima'),
                    ])->columns(2),

                Forms\Components\Section::make('Dosen Pembimbing')
                    ->schema([
                        Forms\Components\Select::make('dosen_pembimbing')
                            ->label('Dosen Pembimbing')
                            ->options(function () {
                                $dosenOptions = [];
                                $dosenList = \App\Models\Auth\DosenPembimbingModel::query()
                                    ->with('bidangKeahlian', 'user')
                                    ->get();

                                foreach ($dosenList as $dosen) {
                                    $bidangKeahlian = $dosen->bidangKeahlian->pluck('nama_bidang_keahlian')->toArray();
                                    $bidangText = !empty($bidangKeahlian)
                                        ? implode(', ', $bidangKeahlian)
                                        : 'Tidak Ada';

                                    $dosenOptions[$dosen->id_dospem] = $dosen->user->nama . ' (' . $bidangText . ')';
                                }

                                return $dosenOptions;
                            })
                            ->searchable()
                            ->required(fn(Forms\Get $get) => $get('status') === 'Diterima')
                            ->visible(fn(Forms\Get $get) => $get('status') === 'Diterima'),
                    ])
                    ->visible(fn(Forms\Get $get) => $get('status') === 'Diterima')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.user.nama')
                    ->label('Nama Mahasiswa')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('lowongan.judul_lowongan')
                    ->label('Judul Lowongan')
                    ->limit(10)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->sortable()
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('tanggal_diterima')
                    ->label('Tanggal Diterima')
                    ->sortable()
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'warning' => 'Diajukan',
                        'success' => 'Diterima',
                        'danger' => 'Ditolak',
                    ])
                    ->searchable()
                    ->sortable(),
            ])
            ->defaultSort('tanggal_pengajuan', 'desc')
            ->emptyStateHeading('Belum ada kegiatan magang yang diajukan')
            ->emptyStateIcon('heroicon-o-document-text')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('Hapus Pengajuan Magang')
                    ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan magang ini? Jika pengajuan sudah diterima, data penempatan magang terkait juga akan dihapus.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal')
                    ->before(function (PengajuanMagangModel $record) {
                        if ($record->penempatan) {
                            DB::table('r_bimbingan')
                                ->where('id_penempatan', $record->penempatan->id_penempatan)
                                ->delete();

                            $record->penempatan->delete();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Hapus Pengajuan Magang')
                        ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan magang ini? Jika pengajuan sudah diterima, data penempatan magang terkait juga akan dihapus.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal')
                        ->before(function (PengajuanMagangModel $record) {
                            if ($record->penempatan) {
                                DB::table('r_bimbingan')
                                    ->where('id_penempatan', $record->penempatan->id_penempatan)
                                    ->delete();

                                $record->penempatan->delete();
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKegiatanMagangs::route('/'),
            'create' => Pages\CreateKegiatanMagang::route('/create'),
            'view' => Pages\ViewKegiatanMagang::route('/{record}'),
            'edit' => Pages\EditKegiatanMagang::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['mahasiswa.user', 'mahasiswa.prodi', 'lowongan.perusahaan', 'lowongan.bidangKeahlian']);
    }
}
