<?php

namespace App\Filament\Mahasiswa\Resources;

use App\Filament\Mahasiswa\Resources\PengajuanMagangMandiriResource\Pages;
use App\Models\Auth\AdminModel;
use App\Models\Reference\PengajuanMagangModel;
use App\Models\Reference\DaerahMagangModel;
use App\Models\Reference\WaktuMagangModel;
use App\Models\Reference\JenisMagangModel;
use App\Models\Reference\InsentifModel;
use App\Models\Reference\BidangKeahlianModel;
use App\Models\Reference\ProvinsiModel;
use App\Models\Reference\PeriodeModel;
use App\Models\Reference\PerusahaanModel;
use App\Models\Reference\LowonganMagangModel;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class PengajuanMagangMandiriResource extends Resource
{
    protected static ?string $model = PengajuanMagangModel::class;

    protected static ?string $navigationIcon = 'heroicon-s-building-office-2';
    protected static ?string $navigationLabel = 'Pengajuan Magang Mandiri';
    protected static ?string $pluralModelLabel = 'Data Pengajuan Magang Mandiri';
    protected static ?string $modelLabel = 'Pengajuan Magang Mandiri';
    protected static ?string $slug = 'pengajuan-magang-mandiri';
    protected static ?string $navigationGroup = 'Pencarian Magang';
    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists() ?? false;
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id_mahasiswa', Auth::user()->mahasiswa->id_mahasiswa ?? null)
            ->whereHas('lowongan', function ($query) {
                $query->where('id_jenis_magang', 4);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pilihan Perusahaan')
                    ->schema([
                        Select::make('perusahaan_tipe')
                            ->label('Jenis Pengajuan Perusahaan')
                            ->options([
                                'baru' => 'Perusahaan Baru (Belum Terdaftar)',
                                'lama' => 'Perusahaan Sudah Terdaftar',
                            ])
                            ->default('lama')
                            ->required()
                            ->reactive(),
                        Select::make('id_perusahaan_lama')
                            ->label('Pilih Perusahaan')
                            ->options(function () {
                                return PerusahaanModel::where('partnership', '=', 'Perusahaan Mitra')
                                    ->orderBy('nama', 'asc')
                                    ->get()
                                    ->pluck('nama', 'id_perusahaan');
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn(callable $get) => $get('perusahaan_tipe') === 'lama')
                            ->required(fn(callable $get) => $get('perusahaan_tipe') === 'lama')
                            ->reactive(),
                    ])
                    ->columns(2),

                Section::make('Informasi Perusahaan')
                    ->schema([
                        TextInput::make('nama_perusahaan')
                            ->label('Nama Perusahaan')
                            ->required(fn(callable $get) => $get('perusahaan_tipe') === 'baru')
                            ->placeholder('Nama Perusahaan Baru')
                            ->maxLength(100),

                        Textarea::make('alamat_perusahaan')
                            ->label('Alamat Perusahaan')
                            ->required(fn(callable $get) => $get('perusahaan_tipe') === 'baru')
                            ->placeholder('Alamat lengkap perusahaan')
                            ->rows(2),

                        TextInput::make('no_telepon_perusahaan')
                            ->label('Nomor Telepon Perusahaan')
                            ->tel()
                            ->required(fn(callable $get) => $get('perusahaan_tipe') === 'baru')
                            ->placeholder('08123456789'),

                        TextInput::make('email_perusahaan')
                            ->label('Email Perusahaan')
                            ->email()
                            ->required(fn(callable $get) => $get('perusahaan_tipe') === 'baru')
                            ->placeholder('bM8oF@example.com'),

                        TextInput::make('website_perusahaan')
                            ->label('Website Perusahaan')
                            ->required(fn(callable $get) => $get('perusahaan_tipe') === 'baru')
                            ->placeholder('www.example.com'),
                    ])
                    ->visible(fn(callable $get) => $get('perusahaan_tipe') === 'baru')
                    ->columns(2),

                Section::make('Detail Magang')
                    ->schema([
                        TextInput::make('judul_lowongan')
                            ->label('Posisi/Jabatan Magang')
                            ->required()
                            ->maxLength(150),

                        Forms\Components\Hidden::make('id_jenis_magang')
                            ->default(4), // ID untuk Magang Mandiri

                        Select::make('id_waktu_magang')
                            ->label('Waktu Magang')
                            ->options(WaktuMagangModel::all()->pluck('waktu_magang', 'id_waktu_magang'))
                            ->required()
                            ->searchable(),

                        Select::make('id_periode')
                            ->label('Periode')
                            ->options(PeriodeModel::all()->pluck('nama_periode', 'id_periode'))
                            ->required()
                            ->searchable(),

                        Select::make('id_insentif')
                            ->label('Insentif')
                            ->options(InsentifModel::all()->pluck('keterangan', 'id_insentif'))
                            ->required()
                            ->searchable(),

                        Select::make('bidang_keahlian')
                            ->label('Bidang Keahlian')
                            ->multiple()
                            ->options(BidangKeahlianModel::all()->pluck('nama_bidang_keahlian', 'id_bidang'))
                            ->required()
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Lokasi Magang')
                    ->schema([
                        Select::make('id_provinsi')
                            ->label('Provinsi')
                            ->options(ProvinsiModel::all()->pluck('nama_provinsi', 'id_provinsi'))
                            ->searchable()
                            ->native(false)
                            ->reactive() // TRIGGER DROPDOWN DAERAH 
                            ->afterStateHydrated(function ($state, callable $set, $record) {
                                if ($record && $record->daerahMagang) {
                                    $provinsiId = $record->daerahMagang->id_provinsi;
                                    $set('id_provinsi', $provinsiId);
                                }
                            })
                            ->required(),

                        Select::make('id_daerah_magang')
                            ->label('Daerah (Kota/Kabupaten)')
                            ->options(function (callable $get) {
                                $provinsiId = $get('id_provinsi');
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
                                if (!$state && $record) {
                                    $set('id_daerah_magang', $record->id_daerah_magang);
                                }
                            })
                            ->reactive(),
                    ])
                    ->columns(2),

                Section::make('Deskripsi dan Jadwal')
                    ->schema([
                        RichEditor::make('deskripsi_lowongan')
                            ->label('Deskripsi Magang')
                            ->required()
                            ->helperText('Jelaskan tugas, tanggung jawab, dan kegiatan yang akan dilakukan selama magang.')
                            ->columnSpanFull(),

                        DatePicker::make('tanggal_mulai')
                            ->label('Tanggal Mulai Magang')
                            ->required(),

                        DatePicker::make('tanggal_selesai')
                            ->label('Tanggal Selesai Magang')
                            ->required()
                            ->after('tanggal_mulai'),

                        DatePicker::make('tanggal_pengajuan')
                            ->label('Tanggal Pengajuan')
                            ->default(now())
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lowongan.judul_lowongan')
                    ->label('Posisi Magang')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('lowongan.perusahaan.nama')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'primary' => 'Diajukan',
                        'success' => 'Diterima',
                        'danger' => 'Ditolak'
                    ])
                    ->label('Status')
                    ->sortable(),
            ])
            ->defaultSort('tanggal_pengajuan', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Diajukan' => 'Diajukan',
                        'Diterima' => 'Diterima',
                        'Ditolak' => 'Ditolak',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pengajuan Magang Mandiri')
                    ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan magang mandiri ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->modalCancelActionLabel('Batal')
                    ->before(function ($record) {
                        if ($record->status !== 'Diajukan') {
                            Notification::make()
                                ->warning()
                                ->title('Tidak dapat menghapus pengajuan')
                                ->body('Pengajuan yang sudah diproses tidak dapat dihapus.')
                                ->persistent()
                                ->send();

                            return false;
                        }

                        if ($record->penempatan()->exists()) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak dapat menghapus pengajuan')
                                ->body('Pengajuan ini sudah memiliki data penempatan.')
                                ->persistent()
                                ->send();

                            return false;
                        }
                    }),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada pengajuan magang mandiri')
            ->emptyStateDescription('Buat pengajuan magang mandiri untuk perusahaan yang tidak terdaftar dalam sistem')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Ajukan Magang Mandiri')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->modalHeading('Ajukan Magang Mandiri Baru'),
            ]);
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
            'index' => Pages\ListPengajuanMagangMandiris::route('/'),
            'create' => Pages\CreatePengajuanMagangMandiri::route('/create'),
            'view' => Pages\ViewPengajuanMagangMandiri::route('/{record}'),
        ];
    }
}
