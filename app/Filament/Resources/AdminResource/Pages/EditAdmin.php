<?php

namespace App\Filament\Resources\AdminResource\Pages;

use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\AdminResource;
use App\Models\Auth\MahasiswaModel;
use App\Models\Auth\AdminModel;
use App\Models\Auth\DosenPembimbingModel;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->id_role == 2) {
            $data['nim'] = $this->record->mahasiswa?->nim ?? '';
        } elseif ($this->record->id_role == 1) {
            $data['nip'] = $this->record->admin?->nip ?? '';
        } elseif ($this->record->id_role == 3) {
            $data['nip'] = $this->record->dosenPembimbing?->nip ?? '';
        }
        return $data;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('nama')->label('Nama Pengguna')->required(),

            Forms\Components\TextInput::make('nim')
                ->label('NIM')
                ->visible($this->record->id_role == 2)
                ->required(),

            Forms\Components\TextInput::make('nip')
                ->label('NIP')
                ->visible(in_array($this->record->id_role, [1, 3]))
                ->required(),

            Forms\Components\TextInput::make('alamat')->label('Alamat')->required(),
            Forms\Components\TextInput::make('no_telepon')->label('No Telepon')->required(),
        ];
    }

    protected function afterSave(): void
    {
        $user = $this->record;
        $roleId = $user->id_role;

        if ($roleId == 2) {
            $mahasiswa = MahasiswaModel::where('id_user', $user->id_user)->first();
            if ($mahasiswa) {
                $mahasiswa->update([
                    'nim' => $this->data['nim'],
                ]);
            } else {
                MahasiswaModel::create([
                    'id_user' => $user->id_user,
                    'nim' => $this->data['nim'],
                    'id_prodi' => 1,
                    'ipk' => 0,
                    'semester' => 1,
                ]);
            }
        } elseif ($roleId == 1) {
            $admin = AdminModel::where('id_user', $user->id_user)->first();
            if ($admin) {
                $admin->update(['nip' => $this->data['nip']]);
            } else {
                AdminModel::create([
                    'id_user' => $user->id_user,
                    'nip' => $this->data['nip'],
                ]);
            }
        } elseif ($roleId == 3) {
            $dospem = DosenPembimbingModel::where('id_user', $user->id_user)->first();
            if ($dospem) {
                $dospem->update(['nip' => $this->data['nip']]);
            } else {
                DosenPembimbingModel::create([
                    'id_user' => $user->id_user,
                    'nip' => $this->data['nip'],
                ]);
            }
        }
    }
}
