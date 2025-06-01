<?php

namespace App\Filament\Resources\AdminResource\Pages;

use Filament\Forms;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\AdminResource;

class ViewAdmin extends ViewRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getViewFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('nama')
                ->label('Nama Pengguna')
                ->disabled(),

            Forms\Components\TextInput::make('nim')
                ->label('NIM')
                ->default(fn ($record) => $record->mahasiswa?->nim ?? '')
                ->visible(fn ($record) => $record->id_role == 2)
                ->disabled(),

            Forms\Components\TextInput::make('nip')
                ->label('NIP')
                ->default(fn ($record) => $record->admin?->nip ?? $record->dosenPembimbing?->nip ?? '')
                ->visible(fn ($record) => in_array($record->id_role, [1, 3]))
                ->disabled(),

            Forms\Components\TextInput::make('alamat')
                ->label('Alamat')
                ->disabled(),

            Forms\Components\TextInput::make('no_telepon')
                ->label('No Telepon')
                ->disabled(),
        ];
    }

    protected function getViewForm(): Forms\Form
    {
        return $this->makeForm()
            ->schema($this->getViewFormSchema())
            ->model($this->getRecord());
    }

    /**
     * Load relasi agar data nim/nip muncul.
     */
    protected function resolveRecord(string|int $key): \Illuminate\Database\Eloquent\Model
    {
        return parent::resolveRecord($key)->load(['mahasiswa', 'admin', 'dosenPembimbing']);
    }
}
