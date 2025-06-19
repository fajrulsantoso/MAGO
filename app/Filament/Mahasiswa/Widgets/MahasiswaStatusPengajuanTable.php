<?php

namespace App\Filament\Mahasiswa\Widgets;

use App\Models\Reference\JenisMagangModel;
use App\Models\Reference\PengajuanMagangModel;
use App\Models\Reference\PerusahaanModel;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MahasiswaStatusPengajuanTable extends BaseWidget
{
    protected static ?int $sort = 1;
    protected static ?string $heading = 'Status Pengajuan Magang Saya';

    public static function canView(): bool
    {
        return auth()->user()?->mahasiswa?->preferensi()->exists();
    }
    
    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    // public function getTitle(): string
    // {
    //     return 'Status Pengajuan Magang Saya';
    // }

    public function table(Table $table): Table
    {
        $userId = Auth::user()->mahasiswa->id_mahasiswa;

        return $table
            ->query(
                // Query hanya pengajuan milik mahasiswa yang sedang login
                PengajuanMagangModel::query()
                    ->where('id_mahasiswa', $userId)
                    ->with([
                        'mahasiswa',
                        'lowongan.perusahaan',
                        'lowongan.jenisMagang',
                        'lowongan.daerahMagang',
                        'penempatan.dosenPembimbing.user',
                        'penempatan.dosenPembimbing.bidangKeahlian'
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('mahasiswa.user.nama')
                    ->label('Mahasiswa'),

                Tables\Columns\TextColumn::make('lowongan.judul_lowongan')
                    ->label('Lowongan')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->lowongan->judul_lowongan ?? '-'),

                Tables\Columns\TextColumn::make('lowongan.jenisMagang.nama_jenis_magang')
                    ->label('Jenis Magang')
                    ->searchable(),

                Tables\Columns\TextColumn::make('lowongan.daerahMagang.namaLengkap')
                    ->label('Lokasi Magang')
                    ->limit(10)
                    ->tooltip(fn($record) => $record->lowongan->daerahMagang->namaLengkap ?? '-'),

                // Kolom tersembunyi untuk pencarian
                Tables\Columns\TextColumn::make('lowongan.daerahMagang.nama_daerah')
                    ->label('Nama Daerah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan secara default

                // Kolom tersembunyi untuk pencarian jenis daerah
                Tables\Columns\TextColumn::make('lowongan.daerahMagang.jenis_daerah')
                    ->label('Jenis Daerah')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan secara default

                Tables\Columns\TextColumn::make('lowongan.perusahaan.nama')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->colors([
                        'primary' => 'Diproses',
                        'success' => 'Diterima',
                        'danger' => 'Ditolak',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_jenis_magang')
                    ->label('Jenis Magang')
                    ->options(function () use ($userId) {
                        // Ambil hanya jenis magang yang terkait dengan pengajuan mahasiswa ini
                        return JenisMagangModel::query()
                            ->whereHas('lowonganMagang.pengajuanMagang', function (Builder $query) use ($userId) {
                                $query->where('id_mahasiswa', $userId);
                            })
                            ->pluck('nama_jenis_magang', 'id_jenis_magang')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) {
                            return $query;
                        }

                        return $query->whereHas('lowongan', function (Builder $q) use ($data) {
                            $q->where('id_jenis_magang', $data['value']);
                        });
                    }),

                Tables\Filters\SelectFilter::make('id_perusahaan')
                    ->label('Perusahaan')
                    ->options(function () use ($userId) {
                        // Ambil hanya perusahaan yang terkait dengan pengajuan mahasiswa ini
                        return PerusahaanModel::query()
                            ->whereHas('lowonganMagang.pengajuanMagang', function (Builder $query) use ($userId) {
                                $query->where('id_mahasiswa', $userId);
                            })
                            ->pluck('nama', 'id_perusahaan')
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) {
                            return $query;
                        }

                        return $query->whereHas('lowongan', function (Builder $q) use ($data) {
                            $q->where('id_perusahaan', $data['value']);
                        });
                    }),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Diajukan' => 'Diajukan',
                        'Diterima' => 'Diterima',
                        'Ditolak' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->infolist([
                        Infolists\Components\Section::make('Informasi Pengajuan')
                            ->schema([
                                Infolists\Components\TextEntry::make('status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'Diterima' => 'success',
                                        'Ditolak' => 'danger',
                                        default => 'primary',
                                    }),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal Pengajuan')
                                    ->dateTime('d M Y'),
                                Infolists\Components\TextEntry::make('tanggal_diterima')
                                    ->label('Tanggal Diterima/Ditolak')
                                    ->dateTime('d M Y')
                                    ->placeholder('-'),
                            ])
                            ->columns(3),

                        Infolists\Components\Section::make('Detail Lowongan')
                            ->schema([
                                Infolists\Components\TextEntry::make('lowongan.judul_lowongan')
                                    ->label('Judul Lowongan'),
                                Infolists\Components\TextEntry::make('lowongan.perusahaan.nama')
                                    ->label('Perusahaan'),
                                Infolists\Components\TextEntry::make('lowongan.jenisMagang.nama_jenis_magang')
                                    ->label('Jenis Magang'),
                                Infolists\Components\TextEntry::make('lowongan.daerahMagang.namaLengkap')
                                    ->label('Lokasi Magang'),
                                Infolists\Components\TextEntry::make('lowongan.deskripsi_lowongan')
                                    ->label('Deskripsi')
                                    ->columnSpanFull()
                                    ->markdown(),
                            ])
                            ->columns(2),

                        Infolists\Components\Section::make('Data Mahasiswa')
                            ->schema([
                                Infolists\Components\TextEntry::make('mahasiswa.user.nama')
                                    ->label('Nama'),
                                Infolists\Components\TextEntry::make('mahasiswa.nim')
                                    ->label('NIM'),
                                Infolists\Components\TextEntry::make('mahasiswa.ipk')
                                    ->label('IPK'),
                                Infolists\Components\TextEntry::make('mahasiswa.semester')
                                    ->label('Semester'),
                            ])
                            ->columns(2),

                        Infolists\Components\Section::make('Dosen Pembimbing')
                            ->schema([
                                Infolists\Components\TextEntry::make('penempatan.dosenPembimbing.user.nama')
                                    ->label('Nama Dosen'),
                                Infolists\Components\TextEntry::make('penempatan.dosenPembimbing.nip')
                                    ->label('NIP'),
                                Infolists\Components\TextEntry::make('penempatan.dosenPembimbing.user.no_telepon')
                                    ->label('No Telepon Dosen'),
                                Infolists\Components\TextEntry::make('penempatan.dosenPembimbing.bidangKeahlian')
                                    ->label('Bidang Keahlian')
                                    ->state(function ($record) {
                                        if (!$record->penempatan || !$record->penempatan->dosenPembimbing) {
                                            return '-';
                                        }

                                        $dospem = $record->penempatan->dosenPembimbing->first();

                                        if (!$dospem || !$dospem->bidangKeahlian || $dospem->bidangKeahlian->isEmpty()) {
                                            return '-';
                                        }

                                        // Mengembalikan array bidang keahlian, bukan string
                                        return $dospem->bidangKeahlian->pluck('nama_bidang_keahlian')->toArray();
                                    })
                                    ->listWithLineBreaks() // Gunakan ini sebagai pengganti bulleted()
                            ])
                            ->columns(3)
                            ->visible(fn($record) => $record->status === 'Diterima'),

                        Infolists\Components\Section::make('Alasan Penolakan')
                            ->schema([
                                Infolists\Components\TextEntry::make('alasan_penolakan')
                                    ->label('Alasan Penolakan')
                                    ->state(function ($record) {
                                        if (empty($record->alasan_penolakan)) {
                                            return 'Tidak memenuhi syarat atau alasan lainnya.';
                                        }

                                        return strip_tags($record->alasan_penolakan);
                                    }),
                            ])
                            ->visible(fn($record) => $record->status === 'Ditolak')
                            ->collapsible(),
                    ]),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Pengajuan Magang')
                    ->modalDescription('Apakah Anda yakin ingin menghapus pengajuan magang ini?')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->before(function ($record, $action) {
                        if ($record->penempatan()->exists()) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak dapat menghapus pengajuan')
                                ->body('Pengajuan ini tidak dapat dihapus karena sudah digunakan dalam data penempatan magang.')
                                ->persistent()
                                ->send();

                            $action->cancel();
                            return false;
                        }

                        if ($record->status === 'Diterima') {
                            Notification::make()
                                ->warning()
                                ->title('Tidak dapat menghapus pengajuan')
                                ->body('Pengajuan yang sudah diterima tidak dapat dihapus.')
                                ->persistent()
                                ->send();

                            $action->cancel();
                            return false;
                        }
                    })
                    ->successNotificationTitle('Pengajuan magang berhasil dihapus'),
            ])
            ->emptyStateHeading('Tidak ada pengajuan magang yang ditemukan')
            ->emptyStateDescription('Silakan ajukan magang baru untuk melihat statusnya.');
    }
}
