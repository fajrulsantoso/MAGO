<?php

namespace App\Filament\Mahasiswa\Resources;

use App\Models\Reference\PerusahaanModel;
use App\Models\Reference\LowonganMagangModel;
use App\Models\Reference\PengajuanMagangModel;
use App\Filament\Mahasiswa\Resources\LamarMagangMahasiswaResource\Pages;
// use App\Filament\Mahasiswa\Widgets\MahasiswaStatusPengajuanTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LamarMagangMahasiswaResource extends Resource
{
    protected static ?string $model = PengajuanMagangModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-rocket-launch';
    protected static ?string $modelLabel = 'Lamaran Magang';
    protected static ?string $pluralModelLabel = 'Data Lamaran Magang';
    protected static ?string $navigationLabel = 'Lamaran Magang';
    protected static ?string $slug = 'lamaran-magang';
    protected static ?string $navigationGroup = 'Histori Lamaran';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Lamaran Magang')
                    ->description('Pilih perusahaan mitra dan lowongan yang tersedia.')
                    ->schema([
                        Select::make('id_perusahaan')
                            ->label('Perusahaan Mitra')
                            ->options(
                                PerusahaanModel::query()
                                    ->where('partnership', 'Perusahaan Mitra')
                                    ->pluck('nama', 'id_perusahaan')
                            )
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('id_lowongan', null)),

                        Select::make('id_lowongan')
                            ->label('Lowongan Magang')
                            ->options(function (Get $get): Collection {
                                $idPerusahaan = $get('id_perusahaan');

                                if (!$idPerusahaan) {
                                    return collect();
                                }

                                return LowonganMagangModel::query()
                                    ->where('id_perusahaan', $idPerusahaan)
                                    ->where('status', 'Aktif')
                                    ->whereHas('jenisMagang', function (Builder $query) {
                                        $query->where('nama_jenis_magang', '!=', 'Magang Mandiri');
                                    })
                                    ->pluck('judul_lowongan', 'id_lowongan');
                            })
                            ->required()
                            ->searchable()
                            ->loadingMessage('Memuat lowongan...')
                            ->placeholder('Pilih lowongan magang')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Forms\Components\Component $component) {
                                if (!$state) {
                                    $component->getContainer()->getComponent('lowongan_preview')->schema([]);
                                    return;
                                }

                                $lowongan = LowonganMagangModel::find($state);
                                if (!$lowongan) return;

                                $component->getContainer()->getComponent('lowongan_preview')->schema([
                                    Placeholder::make('judul_lowongan')
                                        ->label('Judul Lowongan')
                                        ->content($lowongan->judul_lowongan),

                                    Placeholder::make('perusahaan')
                                        ->label('Perusahaan')
                                        ->content($lowongan->perusahaan->nama ?? '-'),

                                    Placeholder::make('jenis_magang')
                                        ->label('Jenis Magang')
                                        ->content($lowongan->jenisMagang->nama_jenis_magang ?? '-'),

                                    Placeholder::make('lokasi')
                                        ->label('Lokasi')
                                        ->content(
                                            ($lowongan->daerahMagang->provinsi->nama_provinsi ?? '-') . ', ' .
                                                ($lowongan->daerahMagang->namaLengkap ?? '-')
                                        ),

                                    Placeholder::make('periode')
                                        ->label('Periode')
                                        ->content($lowongan->periode->nama_periode ?? '-'),

                                    Placeholder::make('waktu_magang')
                                        ->label('Waktu Magang')
                                        ->content($lowongan->waktuMagang->waktu_magang ?? '-'),

                                    Placeholder::make('insentif')
                                        ->label('Insentif')
                                        ->content($lowongan->insentif->keterangan ?? '-'),

                                    Placeholder::make('batas_lamaran')
                                        ->label('Batas Akhir Lamaran')
                                        ->content(
                                            $lowongan->batas_akhir_lamaran
                                                ? $lowongan->batas_akhir_lamaran->format('d F Y')
                                                : '-'
                                        ),

                                    Placeholder::make('status')
                                        ->label('Status')
                                        ->content($lowongan->status ? ucfirst($lowongan->status) : '-'),

                                    Placeholder::make('deskripsi')
                                        ->label('Deskripsi')
                                        ->content(new HtmlString($lowongan->deskripsi_lowongan ?? '-'))
                                        ->columnSpanFull(),
                                ])->columns(2);
                            }),

                        Section::make('Preview Lowongan')
                            ->schema([])
                            ->visible(fn(Get $get) => (bool) $get('id_lowongan'))
                            ->key('lowongan_preview'),

                        Forms\Components\DatePicker::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->default(now())
                            ->required(),

                        Forms\Components\Hidden::make('status')
                            ->default('Diajukan'),

                        Forms\Components\Hidden::make('id_mahasiswa')
                            ->default(fn() => auth()->user()->mahasiswa->id_mahasiswa),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_pengajuan', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('lowongan.judul_lowongan')
                    ->label('Posisi Magang')
                    ->limit(15)
                    ->tooltip(fn($record) => $record->lowongan->judul_lowongan ?? '-')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('lowongan.jenisMagang.nama_jenis_magang')
                    ->label('Jenis Magang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lowongan.perusahaan.nama')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Diajukan' => 'info',
                        'Diterima' => 'success',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->emptyStateHeading('Belum ada lamaran magang')
            ->emptyStateDescription('Ajukan lamaran magang baru untuk melihat daftar lamaran Anda.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Lamar Magang Baru')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->modalHeading('Lamar Magang Baru'),
            ])
            ->filters([
                // Anda bisa menambahkan filter lain di sini jika perlu (misal filter berdasarkan Status)
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->status !== 'Diterima'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id_mahasiswa', Auth::user()->mahasiswa->id_mahasiswa ?? null)
            ->whereHas('lowongan', function ($query) {
                $query->where('id_jenis_magang', '!=', 4);
            });
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLamarMagangMahasiswas::route('/'),
            'create' => Pages\CreateLamarMagangMahasiswa::route('/create'),
            'view' => Pages\ViewLamarMagangMahasiswa::route('/{record}'),
        ];
    }
}
