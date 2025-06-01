<?php

namespace App\Filament\Resources;

use App\Models\UserModel;
use App\Models\Auth\RoleModel;
use App\Filament\Resources\AdminResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdminResource extends Resource
{
    protected static ?string $model = UserModel::class;

    protected static ?string $navigationLabel = 'Manajemen Pengguna';
    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $modelLabel = 'Pengguna';
    protected static ?string $pluralModelLabel = 'Data Pengguna';
    protected static ?string $navigationGroup = 'Reference Data';
    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['role', 'mahasiswa', 'admin', 'dosenPembimbing']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Pengguna')
                            ->required()
                            ->disabled(fn ($get) => !$get('id_role'))
                            ->reactive(),

                        Forms\Components\Select::make('id_role')
                            ->label('Role')
                            ->options(RoleModel::all()->pluck('nama_role', 'id_role'))
                            ->required()
                            ->reactive(),

                        Forms\Components\TextInput::make('nim')
                            ->label('NIM')
                            ->visible(fn ($get) => $get('id_role') == 2)
                            ->required(fn ($get) => $get('id_role') == 2)
                            ->disabled(fn ($get) => !$get('id_role'))
                            ->default(fn ($record) => $record?->mahasiswa?->nim ?? null)
                            ->reactive(),

                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->visible(fn ($get) => in_array($get('id_role'), [1, 3]))
                            ->required(fn ($get) => in_array($get('id_role'), [1, 3]))
                            ->disabled(fn ($get) => !$get('id_role'))
                            ->default(fn ($record) => $record?->admin?->nip ?? $record?->dosenPembimbing?->nip ?? null)
                            ->reactive(),

                        Forms\Components\TextInput::make('alamat')
                            ->label('Alamat')
                            ->disabled(fn ($get) => !$get('id_role'))
                            ->reactive(),

                        Forms\Components\TextInput::make('no_telepon')
                            ->label('No Telepon')
                            ->required()
                            ->disabled(fn ($get) => !$get('id_role'))
                            ->reactive(),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateAdmin)
                            ->disabled(fn ($get) => !$get('id_role'))
                            ->reactive(),

                        Forms\Components\FileUpload::make('profile_picture')
                            ->label('Foto Profil')
                            ->image()
                            ->directory('profile_pictures')
                            ->disk('public')
                            ->disabled(), // masih disabled
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role.nama_role')
                    ->label('Role')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('mahasiswa.nim')
                    ->label('NIM')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('admin.nip')
                    ->label('NIP Admin')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dosenPembimbing.nip')
                    ->label('NIP Dosen Pembimbing')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('no_telepon')
                    ->label('No Telepon')
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('profile_picture')
                    ->label('Foto Profil')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(asset('storage/profile_pictures/default.png')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_role')
                    ->label('Role')
                    ->relationship('role', 'nama_role'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('Tidak ada data pengguna')
            ->emptyStateIcon('heroicon-s-user-group');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'view' => Pages\ViewAdmin::route('/{record}'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
