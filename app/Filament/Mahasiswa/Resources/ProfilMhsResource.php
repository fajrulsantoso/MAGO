<?php

namespace App\Filament\Mahasiswa\Resources;

use App\Filament\Mahasiswa\Resources\ProfilMhsResource\Pages;
use App\Filament\Mahasiswa\Resources\ProfilMhsResource\RelationManagers;
use App\Models\Auth\MahasiswaModel;
use App\Models\ProfilMhs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfilMhsResource extends Resource
{
    protected static ?string $model = MahasiswaModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-user';
    protected static ?string $navigationLabel = 'Profil';
    protected static ?string $pluralModelLabel = 'Profil Saya';
    protected static ?string $navigationGroup = 'Tentang Saya';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             //
    //         ]);
    // }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             //
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ViewProfilMhs::route('/'),
            'edit' => Pages\ProfilMhs::route('/edit'),
        ];
    }


    // public static function getNavigationLabel(): string
    // {
    //     return 'Profil';
    // }

    // public static function getPluralModelLabel(): string
    // {
    //     return 'Profil Saya';
    // }

    // public static function getModelLabel(): string
    // {
    //     return 'Profil';
    // }
}
