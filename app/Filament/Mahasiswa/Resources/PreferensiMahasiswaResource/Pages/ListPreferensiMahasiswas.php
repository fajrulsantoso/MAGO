<?php

namespace App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource\Pages;

use App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListPreferensiMahasiswas extends ListRecords
{
    protected static string $resource = PreferensiMahasiswaResource::class;

    public function mount(): void
    {
        $mahasiswa = Auth::user()?->mahasiswa;
        $preferensi = $mahasiswa?->preferensi;

        if ($preferensi) {
            $this->redirect(PreferensiMahasiswaResource::getUrl('view', ['record' => $preferensi->getKey()]));
        }
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('New Preferensi Magang')
                ->url(PreferensiMahasiswaResource::getUrl('create'))
                ->button(),
        ];
    }
}
