<?php

namespace App\Filament\Mahasiswa\Resources;

use App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource\Pages;
use App\Filament\Mahasiswa\Resources\PreferensiMahasiswaResource\RelationManagers;
use App\Models\Reference\PreferensiMahasiswaModel;
use App\Models\Reference\BidangKeahlianModel;
use App\Models\Reference\DaerahMagangModel;
use App\Models\Reference\InsentifModel;
use App\Models\Reference\JenisMagangModel;
use App\Models\Reference\LokasiMagangModel;
use App\Models\Reference\ProvinsiModel;
use App\Models\Reference\WaktuMagangModel;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PreferensiMahasiswaResource extends Resource
{
    protected static ?string $model = PreferensiMahasiswaModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Preferensi Profil';
    protected static ?string $pluralModelLabel = 'Preferensi Profil Saya';
    protected static ?string $navigationGroup = 'Tentang Saya';

    public static function getEloquentQuery(): Builder
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) {
            return static::getModel()::query()->whereRaw('1=0');
        }

        return static::getModel()::query()
            ->where('id_mahasiswa', $mahasiswa->id_mahasiswa);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Bidang Keahlian')
                    ->schema([
                        CheckboxList::make('bidangKeahlian')
                            ->label('Pilih Bidang Keahlian')
                            ->options(BidangKeahlianModel::all()->pluck('nama_bidang_keahlian', 'id_bidang'))
                            ->columns(4)
                            ->required(),

                        Select::make('ranking_bidang')
                            ->label('Ranking Bidang Keahlian')
                            ->options([
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                4 => '4',
                                5 => '5',
                            ])
                            ->required(),
                    ]),

                Section::make('Daerah Magang')
                    ->schema([
                        Select::make('id_provinsi')
                            ->label('Pilih Provinsi')
                            ->options(ProvinsiModel::all()->pluck('nama_provinsi', 'id_provinsi'))
                            ->searchable()
                            ->native(false)
                            ->reactive()
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                // If there's a record but no state, set the province from the record
                                if ($record && $record->daerahMagang) {
                                    $provinsiId = $record->daerahMagang->id_provinsi;
                                    $set('id_provinsi', $provinsiId);
                                }
                            })
                            ->required(),

                        Select::make('id_daerah_magang')
                            ->label('Pilih Daerah (Kota/Kabupaten)')
                            ->options(function (callable $get, $record) {
                                $provinsiId = $get('id_provinsi');

                                if (!$provinsiId && $record && $record->daerahMagang) {
                                    $provinsiId = $record->daerahMagang->id_provinsi;
                                }

                                if (!$provinsiId) {
                                    return [];
                                }

                                return DaerahMagangModel::where('id_provinsi', $provinsiId)
                                    ->get()
                                    ->pluck('namaLengkap', 'id_daerah_magang');
                            })
                            ->searchable()
                            ->required()
                            ->disabled(fn(callable $get) => !$get('id_provinsi'))
                            ->native(false)
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                // If we have a record, make sure to set the daerah_magang value
                                if (!$state && $record) {
                                    $set('id_daerah_magang', $record->id_daerah_magang);
                                }
                            })
                            ->reactive(),

                        Select::make('ranking_daerah')
                            ->label('Ranking Daerah')
                            ->options([
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                4 => '4',
                                5 => '5',
                            ])
                            ->required(),
                    ]),

                Section::make('Jenis Magang')
                    ->schema([
                        CheckboxList::make('jenisMagang')
                            ->label('Pilih Jenis Magang')
                            ->options(JenisMagangModel::all()->pluck('nama_jenis_magang', 'id_jenis_magang'))
                            ->columns(3)
                            ->reactive()
                            ->required(),

                        Select::make('ranking_jenis_magang')
                            ->label('Ranking Jenis Magang')
                            ->options([
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                4 => '4',
                                5 => '5',
                            ])
                            ->required(),
                    ]),




                Section::make('Insentif Magang')
                    ->schema([
                        Select::make('id_insentif')
                            ->label('Pilih Insentif')
                            ->options(InsentifModel::all()->pluck('keterangan', 'id_insentif'))
                            ->searchable()
                            ->required()
                            ->native(false),

                        Select::make('ranking_insentif')
                            ->label('Ranking Insentif')
                            ->options([
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                4 => '4',
                                5 => '5',
                            ])
                            ->required(),
                    ]),

                Section::make('Waktu Magang')
                    ->schema([
                        Select::make('id_waktu_magang')
                            ->label('Pilih Waktu Magang')
                            ->options(WaktuMagangModel::all()->pluck('waktu_magang', 'id_waktu_magang'))
                            ->searchable()
                            ->native(false),

                        Select::make('ranking_waktu_magang')
                            ->label('Ranking Waktu Magang')
                            ->options([
                                1 => '1',
                                2 => '2',
                                3 => '3',
                                4 => '4',
                                5 => '5',
                            ])
                            ->required(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('bidangKeahlian.nama_bidang_keahlian')
                    ->label('Bidang Keahlian'),
                TextColumn::make('ranking_bidang')
                    ->label('Ranking Bidang'),
                TextColumn::make('daerahMagang.nama_daerah')
                    ->label('Daerah Magang'),
                TextColumn::make('ranking_daerah')
                    ->label('Ranking Daerah'),
                TextColumn::make('jenisMagang.nama_jenis_magang')
                    ->label('Jenis Magang'),
                TextColumn::make('ranking_jenis_magang')
                    ->label('Ranking Jenis'),
                TextColumn::make('insentif.keterangan')
                    ->label('Insentif'),
                TextColumn::make('ranking_insentif')
                    ->label('Ranking Insentif'),
                TextColumn::make('waktuMagang.waktu_magang')
                    ->label('Waktu Magang'),
                TextColumn::make('ranking_waktu_magang')
                    ->label('Ranking Waktu'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada preferensi magang yang ditemukan')
            ->emptyStateDescription('Silakan buat preferensi magang baru untuk mengatur preferensi magang Anda.');
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
            'index' => Pages\ListPreferensiMahasiswas::route('/'),
            'create' => Pages\CreatePreferensiMahasiswa::route('/create'),
            'edit' => Pages\EditPreferensiMahasiswa::route('/{record}/edit'),
            'view' => Pages\ViewPreferensiMahasiswa::route('/{record}'),
        ];
    }
}
