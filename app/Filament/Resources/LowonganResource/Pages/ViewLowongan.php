<?php

namespace App\Filament\Resources\LowonganResource\Pages;

use App\Filament\Resources\LowonganResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Actions;

class ViewLowongan extends ViewRecord
{
    protected static string $resource = LowonganResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Lowongan')
                    ->schema([
                        Infolists\Components\TextEntry::make('judul_lowongan')
                            ->label('Judul Lowongan'),
                            
                        Infolists\Components\TextEntry::make('perusahaan.nama')
                            ->label('Perusahaan'),
                        
                        Infolists\Components\TextEntry::make('perusahaan.alamat')
                            ->label('Alamat Perusahaan')
                            ->columnSpanFull(),
                            
                        Infolists\Components\TextEntry::make('deskripsi_lowongan')
                            ->label('Deskripsi Lowongan')
                            ->html()
                            ->columnSpanFull(),
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
                            
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Aktif' => 'success',
                                'Selesai' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(3),

                Infolists\Components\Section::make('Tanggal')
                    ->schema([
                        Infolists\Components\TextEntry::make('tanggal_posting')
                            ->label('Tanggal Posting')
                            ->date(),
                            
                        Infolists\Components\TextEntry::make('batas_akhir_lamaran')
                            ->label('Batas Akhir Lamaran')
                            ->date(),
                    ])->columns(2),

                Infolists\Components\Section::make('Bidang Keahlian')
                    ->schema([
                        Infolists\Components\TextEntry::make('bidangKeahlian.nama_bidang_keahlian')
                            ->label('Bidang Keahlian')
                            ->listWithLineBreaks(),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}