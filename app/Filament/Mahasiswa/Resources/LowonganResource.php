<?php

namespace App\Filament\Mahasiswa\Resources;

use App\Filament\Mahasiswa\Resources\LowonganResource\Pages;
use App\Filament\Mahasiswa\Widgets\RekomendasiMagang;
use App\Models\Reference\LowonganMagangModel;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Support\Arr;

class LowonganResource extends Resource
{
    protected static ?string $model = LowonganMagangModel::class;
    protected static ?string $navigationIcon = 'heroicon-s-sparkles';
    protected static ?string $modelLabel = 'Lowongan Magang';
    protected static ?string $pluralModelLabel = 'Data Lowongan Magang';
    protected static ?string $navigationLabel = 'Lowongan Magang';
    protected static ?string $slug = 'lowongan-magang';
    protected static ?string $navigationGroup = 'Pencarian Magang';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }
    
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Detail Lowongan Magang')
                    ->schema([
                        TextEntry::make('judul_lowongan')
                            ->label('Judul Lowongan')
                            ->weight('bold')
                            ->size(TextEntry\TextEntrySize::Large),

                        Infolists\Components\Section::make('Informasi Perusahaan')
                            ->schema([
                                Infolists\Components\TextEntry::make('perusahaan.nama')
                                    ->label('Perusahaan'),

                                Infolists\Components\TextEntry::make('perusahaan.alamat')
                                    ->label('Alamat Perusahaan'),

                                Infolists\Components\TextEntry::make('perusahaan.no_telepon')
                                    ->label('No. Telepon Perusahaan'),

                                Infolists\Components\TextEntry::make('perusahaan.email')
                                    ->label('Email Perusahaan'),

                                Infolists\Components\TextEntry::make('perusahaan.website')
                                    ->label('Website Perusahaan'),
                            ])->columns(2),

                        Infolists\Components\Section::make('Detail Magang')
                            ->schema([
                                Infolists\Components\TextEntry::make('jenisMagang.nama_jenis_magang')
                                    ->label('Jenis Magang'),

                                Infolists\Components\TextEntry::make('daerahMagang.provinsi.nama_provinsi')
                                    ->label('Provinsi'),

                                Infolists\Components\TextEntry::make('daerahMagang.namaLengkap')
                                    ->label('Daerah (Kota/Kabupaten)'),

                                Infolists\Components\TextEntry::make('periode.nama_periode')
                                    ->label('Periode'),

                                Infolists\Components\TextEntry::make('waktuMagang.waktu_magang')
                                    ->label('Waktu Magang'),

                                Infolists\Components\TextEntry::make('insentif.keterangan')
                                    ->label('Insentif'),

                                Infolists\Components\TextEntry::make('tanggal_posting')
                                    ->label('Tanggal Posting')
                                    ->date(),

                                Infolists\Components\TextEntry::make('batas_akhir_lamaran')
                                    ->label('Batas Akhir Lamaran')
                                    ->date(),
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'Aktif' => 'success',
                                        'Selesai' => 'danger',
                                        default => 'gray',
                                    }),

                            ])->columns(3),
                        Infolists\Components\Section::make('Deskripsi Lowongan')
                            ->schema([
                                Infolists\Components\TextEntry::make('deskripsi_lowongan')
                                    ->label('Deskripsi Lowongan')
                                    ->html()
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('bidangKeahlian.nama_bidang_keahlian')
                                    ->label('Bidang Keahlian')
                                    ->listWithLineBreaks(),
                            ])->columns(2),
                    ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            RekomendasiMagang::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLowongans::route('/'),
            'view' => Pages\ViewLowongan::route('/{record}'),
        ];
    }
}
