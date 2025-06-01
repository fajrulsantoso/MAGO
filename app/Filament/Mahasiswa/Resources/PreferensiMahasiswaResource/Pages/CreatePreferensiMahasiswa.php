<?php

namespace App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource\Pages;

use App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource;
use App\Models\Reference\PreferensiMahasiswaModel;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePreferensiMahasiswa extends CreateRecord
{
    protected static string $resource = PreferensiMahasiswaResource::class;

    protected static ?string $title = 'Buat Preferensi Profil Baru';
    protected array $bidangIds = [];
    protected array $jenisIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_mahasiswa'] = Auth::user()->mahasiswa->id_mahasiswa;

        // Simpan bidang & jenis ID ke properti sementara
        $this->bidangIds = $data['bidangKeahlian'];
        unset($data['bidangKeahlian']);

        $this->jenisIds = $data['jenisMagang'];
        unset($data['jenisMagang']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->bidangKeahlian()->sync($this->bidangIds);
        $this->record->jenisMagang()->sync($this->jenisIds);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
}
