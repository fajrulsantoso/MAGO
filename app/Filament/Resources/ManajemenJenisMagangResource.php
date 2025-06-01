<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManajemenJenisMagangResource\Pages;
use App\Filament\Resources\ManajemenJenisMagangResource\RelationManagers;
use App\Models\ManajemenJenisMagang;
use App\Models\Reference\JenisMagangModel;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManajemenJenisMagangResource extends Resource
{
    protected static ?string $model = JenisMagangModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-group';
    protected static ?string $navigationLabel = 'Jenis Magang';
    protected static ?string $slug = 'manajemen-jenis-magang';
    protected static ?string $modelLabel = 'Jenis Magang';
    protected static ?string $pluralModelLabel = 'Data Jenis Magang';
    protected static ?string $navigationGroup = 'Data Referensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_jenis_magang')
                    ->label('Nama Jenis Magang')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_jenis_magang')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_jenis_magang')
                    ->label('Nama')
                    ->sortable()
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
            ->emptyStateHeading('Tidak ada data jenis magang yang ditemukan')
            ->emptyStateDescription('Silakan buat data jenis magang baru.');
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
            'index' => Pages\ListManajemenJenisMagangs::route('/'),
            'create' => Pages\CreateManajemenJenisMagang::route('/create'),
            'edit' => Pages\EditManajemenJenisMagang::route('/{record}/edit'),
        ];
    }
}
