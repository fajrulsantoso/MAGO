<?php

namespace App\Filament\Pembimbing\Resources\ManajemenMahasiswaBimbinganResource\Pages;

use App\Filament\Pembimbing\Resources\ManajemenMahasiswaBimbinganResource;
use Filament\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;

class ViewMahasiswaBimbingan extends ViewRecord
{
    protected static string $resource = ManajemenMahasiswaBimbinganResource::class;

    protected static string $view = 'filament.pembimbing.resources.manajemen-mahasiswa-bimbingan-resource.pages.view-mahasiswa-bimbingan';

    protected static ?string $title = 'Detail Magang Mahasiswa';

    protected static ?string $slug = 'Detail Magang Mahasiswa';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->url(function () {
                    return url()->previous();
                })
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Split::make([
                    // Informasi Mahasiswa
                    Section::make('Informasi Mahasiswa')
                        ->schema([
                            TextEntry::make('mahasiswa.user.nama')
                                ->label('Nama Mahasiswa'),
                            TextEntry::make('mahasiswa.nim')
                                ->label('NIM'),
                            TextEntry::make('mahasiswa.prodi.nama_prodi')
                                ->label('Program Studi'),
                            TextEntry::make('mahasiswa.semester')
                                ->label('Semester'),
                            TextEntry::make('mahasiswa.user.alamat')
                                ->label('Alamat'),
                            TextEntry::make('mahasiswa.user.no_telepon')
                                ->label('No. Telepon'),
                        ])
                        ->columns(2),
                    // Informasi Magang
                    Section::make('Informasi Magang')
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status Magang')
                                ->badge()
                                ->color(fn($state) => match ($state) {
                                    'Berlangsung' => 'primary',
                                    'Selesai'    => 'success',
                                }),
                            TextEntry::make('pengajuan.lowongan.jenisMagang.nama_jenis_magang')
                                ->label('Jenis Magang'),
                            TextEntry::make('pengajuan.lowongan.periode.nama_periode')
                                ->label('Periode'),
                            TextEntry::make('pengajuan.lowongan.waktuMagang.waktu_magang')
                                ->label('Waktu Magang'),
                        ])
                        ->columns(2),
                ])
                    ->from('md') // Split mulai dari medium screen
                    ->columnSpanFull(),



                // Tempat Magang
                Section::make('Lokasi Magang Mahasiswa')
                    ->schema([
                        TextEntry::make('pengajuan.lowongan.perusahaan.nama')
                            ->label('Perusahaan Tempat Magang'),
                        TextEntry::make('pengajuan.lowongan.perusahaan.alamat')
                            ->label('Alamat Perusahaan'),
                        TextEntry::make('pengajuan.lowongan.daerahMagang.provinsi.nama_provinsi')
                            ->label('Provinsi'),
                        TextEntry::make('pengajuan.lowongan.daerahMagang.nama_daerah')
                            ->label('Daerah Magang')
                            ->formatStateUsing(function ($state, $record) {
                                $jenis = $record->pengajuan->lowongan->daerahMagang->jenis_daerah ?? '';
                                $nama = $record->pengajuan->lowongan->daerahMagang->nama_daerah ?? '';
                                return "{$jenis} {$nama}";
                            }),
                        TextEntry::make('pengajuan.lowongan.perusahaan.website')
                            ->label('Website Perusahaan')
                            ->url(fn($state) => $state) // bikin jadi link yang bisa diklik
                            ->color('primary')
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                // Lowongan yang ditempuh (diterima magang)
                Section::make('Lowongan Magang yang sedang Ditempuh')
                    ->schema([
                        TextEntry::make('pengajuan.lowongan.judul_lowongan')
                            ->label('Judul Lowongan'),
                        TextEntry::make('pengajuan.lowongan.bidangKeahlian.nama_bidang_keahlian')
                            ->label('Bidang Keahlian'),
                        TextEntry::make('pengajuan.lowongan.deskripsi_lowongan')
                            ->label('Deskripsi Lowongan')
                            ->html(true)
                            ->columnSpan(3),

                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
