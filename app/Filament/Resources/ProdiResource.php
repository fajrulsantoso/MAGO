<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdiResource\Pages;
use App\Filament\Resources\ProdiResource\RelationManagers;
use App\Models\Reference\ProdiModel;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdiResource extends Resource
{
    protected static ?string $model = ProdiModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-building-library';
    protected static ?string $navigationLabel = 'Program Studi';
    protected static ?string $pluralModelLabel = 'Data Program Studi';
    protected static ?string $modelLabel = 'Program Studi';
    protected static ?string $navigationGroup = 'Data Referensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Buat Program Studi Baru')
                ->schema([
                TextInput::make('nama_prodi')
                    ->label('Nama Program Studi'),
                TextInput::make('kode_prodi')
                    ->label('Kode Program Studi')
                    ->placeholder('Contoh: D4-TI'),
            ])
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_prodi')
                    ->label('Nama Program Studi')
                    ->searchable(),
                TextColumn::make('kode_prodi')
                    ->label('Kode Program Studi')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada data program studi yang ditemukan')
            ->emptyStateDescription('Silakan buat data program studi baru.');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProdis::route('/'),
            'create' => Pages\CreateProdi::route('/create'),
            'edit' => Pages\EditProdi::route('/{record}/edit'),
        ];
    }
}
