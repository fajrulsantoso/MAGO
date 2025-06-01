<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManajemenDaerahMagangResource\Pages;
use App\Filament\Resources\ManajemenDaerahMagangResource\RelationManagers;
use App\Models\ManajemenDaerahMagang;
use App\Models\Reference\DaerahMagangModel;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManajemenDaerahMagangResource extends Resource
{
    protected static ?string $model = DaerahMagangModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-map';
    protected static ?string $navigationLabel = 'Daerah Magang';
    protected static ?string $slug = 'manajemen-daerah-magang';
    protected static ?string $modelLabel = 'Daerah Magang';
    protected static ?string $pluralModelLabel = 'Data Daerah Magang';
    protected static ?string $navigationGroup = 'Data Referensi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_daerah')
                    ->label('Nama Daerah')
                    ->required(),

                Select::make('jenis_daerah')
                    ->label('Jenis Daerah')
                    ->options([
                        'Kabupaten' => 'Kabupaten',
                        'Kota' => 'Kota',
                    ])
                    ->required(),

                Select::make('id_provinsi')
                    ->label('Provinsi')
                    ->relationship('provinsi', 'nama_provinsi')
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_daerah_magang')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama_daerah')
                    ->label('Nama Daerah')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jenis_daerah')
                    ->label('Jenis Daerah')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('provinsi.nama_provinsi')
                    ->label('Provinsi')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('nama_provinsi')
                ->label('Provinsi')
                ->relationship('provinsi', 'nama_provinsi'),

                SelectFilter::make('jenis_daerah')
                ->label('Jenis Daerah')
                ->options([
                    'Kota' => 'Kota',
                    'Kabupaten' => 'Kabupaten'
                ]),
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
            ->emptyStateHeading('Tidak ada data daerah yang ditemukan')
            ->emptyStateDescription('Silakan buat data daerah baru.');
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
            'index' => Pages\ListManajemenDaerahMagangs::route('/'),
            'create' => Pages\CreateManajemenDaerahMagang::route('/create'),
            'edit' => Pages\EditManajemenDaerahMagang::route('/{record}/edit'),
        ];
    }
}
