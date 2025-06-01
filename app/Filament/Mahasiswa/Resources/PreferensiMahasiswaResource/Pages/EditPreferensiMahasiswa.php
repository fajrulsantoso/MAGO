<?php

namespace App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource\Pages;

use App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPreferensiMahasiswa extends EditRecord
{
    protected static string $resource = PreferensiMahasiswaResource::class;

    protected static ?string $title = 'Edit Preferensi Profil Saya';
    protected array $bidangIds = [];
    protected array $jenisIds = [];


    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load relationship data for the form
        if ($this->record) {
            $data['bidangKeahlian'] = $this->record->bidangKeahlian->pluck('id_bidang')->toArray();
            $data['jenisMagang'] = $this->record->jenisMagang->pluck('id_jenis_magang')->toArray();
        }
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['id_mahasiswa'] = Auth::user()->mahasiswa->id_mahasiswa;

        // Simpan relasi sementara
        $this->bidangIds = $data['bidangKeahlian'];
        unset($data['bidangKeahlian']);

        $this->jenisIds = $data['jenisMagang'];
        unset($data['jenisMagang']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->bidangKeahlian()->sync($this->bidangIds);
        $this->record->jenisMagang()->sync($this->jenisIds);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
