<?php

namespace App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource\Pages;

use App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource;
use App\Models\Reference\PreferensiMahasiswaModel;
use Filament\Resources\Pages\Page;
use App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource\Pages\EditPreferensiMahasiswa;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\Action;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;
use Filament\Resources\Pages\ViewRecord;

class ViewPreferensiMahasiswa extends ViewRecord
{
    protected static string $resource = PreferensiMahasiswaResource::class;

    protected static string $view = 'filament.mahasiswa.resources.preferensi-mahasiswa-resource.pages.view-preferensi-mahasiswa';

    protected static ?string $title = 'Preferensi Profil Saya';
    protected static ?string $slug = 'preferensi mahasiswa';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Bidang Keahlian')
                    ->schema([
                        TextEntry::make('bidangKeahlian')
                            ->label('Bidang Keahlian')
                            ->formatStateUsing(fn() => $this->record->bidangKeahlian->pluck('nama_bidang_keahlian')->implode(', ') ?? '-'),
                        TextEntry::make('ranking_bidang')
                            ->label('Tingkat Prioritas Bidang Keahlian')
                            ->formatStateUsing(fn($state) => match ($state) {
                                1 => 'Prioritas Utama',
                                2 => 'Prioritas Tinggi',
                                3 => 'Prioritas Sedang',
                                4 => 'Prioritas Rendah',
                                5 => 'Tidak Diprioritaskan',
                            }),
                    ]),
                Section::make('Daerah Magang')
                    ->schema([
                        TextEntry::make('daerahMagang.nama_daerah')->label('Daerah Magang'),
                        TextEntry::make('ranking_daerah')
                            ->label('Tingkat Prioritas Daerah Magang')
                            ->formatStateUsing(fn($state) => match ($state) {
                                1 => 'Prioritas Utama',
                                2 => 'Prioritas Tinggi',
                                3 => 'Prioritas Sedang',
                                4 => 'Prioritas Rendah',
                                5 => 'Tidak Diprioritaskan',
                            }),
                    ]),
                Section::make('Jenis Magang')
                    ->schema([
                        TextEntry::make('jenisMagang')
                            ->label('Jenis Magang')
                            ->formatStateUsing(fn() => $this->record->jenisMagang->pluck('nama_jenis_magang')->implode(', ') ?? '-'),
                        TextEntry::make('ranking_jenis_magang')
                         ->label('Tingkat Prioritas Jenis Magang')
                            ->formatStateUsing(fn($state) => match ($state) {
                                1 => 'Prioritas Utama',
                                2 => 'Prioritas Tinggi',
                                3 => 'Prioritas Sedang',
                                4 => 'Prioritas Rendah',
                                5 => 'Tidak Diprioritaskan',
                            }),
                    ]),
                Section::make('Insentif')
                    ->schema([
                        TextEntry::make('insentif.keterangan')->label('Insentif'),
                        TextEntry::make('ranking_insentif')
                         ->label('Tingkat Prioritas Insentif')
                            ->formatStateUsing(fn($state) => match ($state) {
                                1 => 'Prioritas Utama',
                                2 => 'Prioritas Tinggi',
                                3 => 'Prioritas Sedang',
                                4 => 'Prioritas Rendah',
                                5 => 'Tidak Diprioritaskan',
                            }),
                    ]),
                Section::make('Waktu Magang')
                    ->schema([
                        TextEntry::make('waktuMagang.waktu_magang')->label('Waktu Magang'),
                        TextEntry::make('ranking_waktu_magang')
                         ->label('Tingkat Prioritas Waktu Magang')
                            ->formatStateUsing(fn($state) => match ($state) {
                                1 => 'Prioritas Utama',
                                2 => 'Prioritas Tinggi',
                                3 => 'Prioritas Sedang',
                                4 => 'Prioritas Rendah',
                                5 => 'Tidak Diprioritaskan',
                            }),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
            ->modalHeading('Delete Preferensi Mahasiswa'),
        ];
    }
}
