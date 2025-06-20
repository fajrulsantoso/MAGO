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
use Closure;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\HtmlString;


class PreferensiMahasiswaResource extends Resource
{
    protected static ?string $model = PreferensiMahasiswaModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Preferensi Profil';
    protected static ?string $pluralModelLabel = 'Preferensi Profil Saya';
    protected static ?string $navigationGroup = 'Tentang Saya';
    protected static ?int $navigationSort = 6;

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
                Placeholder::make('deskripsi')
                    ->label('')
                    ->content(new HtmlString('
                <div class="w-full p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-gray-800 space-y-2">
            <div class="flex items-center gap-2">
                <span class="text-blue-500 text-lg">📋</span>
                <span class="font-semibold text-base">Panduan Preferensi Profil</span>
            </div>
            <p>
                Pada halaman ini, Anda diminta untuk memilih berbagai kriteria preferensi serta tingkat <span class="font-semibold text-blue-600">prioritas</span>-nya.
            </p>
            <p>
                <span class="text-green-600 font-medium">✅ Semakin tinggi prioritas</span> yang Anda pilih, semakin besar bobot yang diberikan pada kriteria tersebut.
            </p>
            <p>
                Bobot ini digunakan untuk menghasilkan <span class="italic font-semibold text-purple-600">rekomendasi lowongan magang</span> 
                yang paling sesuai dengan kebutuhan dan keinginan Anda.
            </p>
        </div>
            '))
                    ->columnSpanFull(),
                Grid::make(2)
                    ->schema([
                        Section::make('Bidang Keahlian')
                            ->schema([
                                CheckboxList::make('bidangKeahlian')
                                    ->label('Pilih Bidang Keahlian')
                                    ->options(BidangKeahlianModel::all()->pluck('nama_bidang_keahlian', 'id_bidang'))
                                    ->columns(2)
                                    ->required(),
                            ])
                            ->columnSpan(1),

                        Section::make('Tingkat Prioritas Bidang Keahlian')
                            ->schema([
                                Radio::make('ranking_bidang')
                                    ->label('')
                                    ->options([
                                        1 => 'Sangat Tinggi',
                                        2 => 'Tinggi',
                                        3 => 'Sedang',
                                        4 => 'Rendah',
                                        5 => 'Tidak Diprioritaskan',
                                    ])
                                    ->required()
                                    ->columns(3)
                                    ->rules(function (callable $get) {
                                        $used = [
                                            $get('ranking_daerah'),
                                            $get('ranking_jenis_magang'),
                                            $get('ranking_insentif'),
                                            $get('ranking_waktu_magang'),
                                        ];

                                        return [
                                            function (string $attribute, $value, Closure $fail) use ($used) {
                                                if (in_array($value, array_filter($used))) {
                                                    $fail("Tingkat prioritas sudah digunakan di bagian lain.");
                                                }
                                            },
                                        ];
                                    }),
                            ])->columnSpan(1),

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
                                    ->reactive()
                            ])->columnSpan(1),

                        Section::make('Tingkat Prioritas Daerah Magang')
                            ->schema([
                                Radio::make('ranking_daerah')
                                    ->label('')
                                    ->options([
                                        1 => 'Sangat Tinggi',
                                        2 => 'Tinggi',
                                        3 => 'Sedang',
                                        4 => 'Rendah',
                                        5 => 'Tidak Diprioritaskan',
                                    ])
                                    ->required()
                                    ->columns(3)
                                    ->rules(function (callable $get) {
                                        $used = [
                                            $get('ranking_bidang'),
                                            $get('ranking_jenis_magang'),
                                            $get('ranking_insentif'),
                                            $get('ranking_waktu_magang'),
                                        ];

                                        return [
                                            function (string $attribute, $value, Closure $fail) use ($used) {
                                                if (in_array($value, array_filter($used))) {
                                                    $fail("Tingkat prioritas sudah digunakan di bagian lain.");
                                                }
                                            },
                                        ];
                                    }),
                            ])->columnSpan(1),

                        Section::make('Jenis Magang')
                            ->schema([
                                CheckboxList::make('jenisMagang')
                                    ->label('Pilih Jenis Magang')
                                    ->options(
                                        JenisMagangModel::where('nama_jenis_magang', '!=', 'Magang Mandiri')
                                            ->pluck('nama_jenis_magang', 'id_jenis_magang')
                                    )
                                    ->columns(2)
                                    ->reactive()
                                    ->required()
                            ])->columnSpan(1),

                        Section::make('Tingkat Prioritas Jenis Magang')
                            ->schema([
                                Radio::make('ranking_jenis_magang')
                                    ->label('')
                                    ->options([
                                        1 => 'Sangat Tinggi',
                                        2 => 'Tinggi',
                                        3 => 'Sedang',
                                        4 => 'Rendah',
                                        5 => 'Tidak Diprioritaskan',
                                    ])
                                    ->required()
                                    ->columns(3)
                                    ->rules(function (callable $get) {
                                        $used = [
                                            $get('ranking_bidang'),
                                            $get('ranking_daerah'),
                                            $get('ranking_insentif'),
                                            $get('ranking_waktu_magang'),
                                        ];

                                        return [
                                            function (string $attribute, $value, Closure $fail) use ($used) {
                                                if (in_array($value, array_filter($used))) {
                                                    $fail("Tingkat prioritas sudah digunakan di bagian lain.");
                                                }
                                            },
                                        ];
                                    }),
                            ])->columnSpan(1),



                        Section::make('Insentif Magang')
                            ->schema([
                                Select::make('id_insentif')
                                    ->label('Pilih Insentif')
                                    ->options(InsentifModel::all()->pluck('keterangan', 'id_insentif'))
                                    ->searchable()
                                    ->required()
                                    ->native(false)
                            ])->columnSpan(1),

                        Section::make('Tingkat Prioritas Insentif')
                            ->schema([
                                Radio::make('ranking_insentif')
                                    ->label('')
                                    ->options([
                                        1 => 'Sangat Tinggi',
                                        2 => 'Tinggi',
                                        3 => 'Sedang',
                                        4 => 'Rendah',
                                        5 => 'Tidak Diprioritaskan',
                                    ])
                                    ->required()
                                    ->columns(3)
                                    ->rules(function (callable $get) {
                                        $used = [
                                            $get('ranking_bidang'),
                                            $get('ranking_daerah'),
                                            $get('ranking_jenis_magang'),
                                            $get('ranking_waktu_magang'),
                                        ];

                                        return [
                                            function (string $attribute, $value, Closure $fail) use ($used) {
                                                if (in_array($value, array_filter($used))) {
                                                    $fail("Tingkat prioritas sudah digunakan di bagian lain.");
                                                }
                                            },
                                        ];
                                    }),
                            ])->columnSpan(1),

                        Section::make('Waktu Magang')
                            ->schema([
                                Select::make('id_waktu_magang')
                                    ->label('Pilih Waktu Magang')
                                    ->options(WaktuMagangModel::all()->pluck('waktu_magang', 'id_waktu_magang'))
                                    ->searchable()
                                    ->native(false)
                            ])->columnSpan(1),

                        Section::make('Tingkat Prioritas Waktu Magang')
                            ->schema([
                                Radio::make('ranking_waktu_magang')
                                    ->label('')
                                    ->options([
                                        1 => 'Sangat Tinggi',
                                        2 => 'Tinggi',
                                        3 => 'Sedang',
                                        4 => 'Rendah',
                                        5 => 'Tidak Diprioritaskan',
                                    ])
                                    ->required()
                                    ->columns(3)
                                    ->rules(function (callable $get) {
                                        $used = [
                                            $get('ranking_bidang'),
                                            $get('ranking_daerah'),
                                            $get('ranking_jenis_magang'),
                                            $get('ranking_insentif'),
                                        ];

                                        return [
                                            function (string $attribute, $value, Closure $fail) use ($used) {
                                                if (in_array($value, array_filter($used))) {
                                                    $fail("Tingkat prioritas sudah digunakan di bagian lain.");
                                                }
                                            },
                                        ];
                                    }),
                            ])->columnSpan(1),
                    ])
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('Delete Preferensi Mahasiswa'),
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
