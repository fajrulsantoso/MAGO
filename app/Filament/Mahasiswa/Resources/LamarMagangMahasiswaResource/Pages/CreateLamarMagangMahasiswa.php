<?php

namespace App\Filament\Mahasiswa\Resources\LamarMagangMahasiswaResource\Pages;

use App\Filament\Mahasiswa\Resources\LamarMagangMahasiswaResource;
use App\Models\Reference\LowonganMagangModel;
use App\Models\Reference\PengajuanMagangModel;
use Filament\Actions;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\Request;

class CreateLamarMagangMahasiswa extends CreateRecord
{
    protected static string $resource = LamarMagangMahasiswaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['tanggal_pengajuan'])) {
            $data['tanggal_pengajuan'] = now()->format('Y-m-d');
        }

        if (!isset($data['id_mahasiswa'])) {
            $data['id_mahasiswa'] = auth()->user()->mahasiswa->id_mahasiswa;
        }

        if (!isset($data['status'])) {
            $data['status'] = 'Diajukan';
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    public function mount(): void
    {
        $request = app(Request::class);
        $lowongan_id = $request->query('id_lowongan');

        parent::mount();

        if ($lowongan_id) {
            $lowongan = LowonganMagangModel::find($lowongan_id);

            if ($lowongan) {
                $this->form->fill([
                    'id_lowongan' => $lowongan_id,
                    'id_perusahaan' => $lowongan->id_perusahaan,
                    'tanggal_pengajuan' => now(),
                    'id_mahasiswa' => auth()->user()->mahasiswa->id_mahasiswa,
                    'status' => 'Diajukan',
                ]);

                $sectionComponent = $this->form->getComponents()[0];
                $childComponents = $sectionComponent->getChildComponents();

                $idPerusahaanComponent = collect($childComponents)->first(fn($component) => $component->getName() === 'id_perusahaan');
                if ($idPerusahaanComponent) {
                    $idPerusahaanComponent->disabled();
                }

                $idLowonganComponent = collect($childComponents)->first(fn($component) => $component->getName() === 'id_lowongan');
                if ($idLowonganComponent) {
                    $idLowonganComponent->disabled();
                }

                foreach ($childComponents as $component) {
                    if (method_exists($component, 'getLabel')) {
                        if ($component->getLabel() == 'Tanggal Pengajuan') {
                            $component->disabled();
                            break;
                        }
                    }
                }

                $lowonganPreview = $this->form->getComponent('lowongan_preview');
                if ($lowonganPreview) {
                    $lowonganPreview->schema([
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
                            ->content(new \Illuminate\Support\HtmlString($lowongan->deskripsi_lowongan ?? '-'))
                            ->columnSpanFull(),
                    ])->columns(2);
                }
            }
        }
    }

    protected function beforeCreate(): void
    {
        $idMahasiswa = auth()->user()->mahasiswa->id_mahasiswa;
        $existingLamaran = PengajuanMagangModel::where('id_mahasiswa', $idMahasiswa)
            ->whereIn('status', ['Diajukan', 'Diterima'])
            ->with(['lowongan'])
            ->get();

        foreach ($existingLamaran as $lamaran) {
            // Jika status masih Diajukan, langsung blok
            if ($lamaran->status === 'Diajukan') {
                Notification::make()
                    ->title('Gagal Mengajukan Lamaran')
                    ->body('Anda sudah memiliki satu lamaran magang aktif. Anda hanya bisa melamar lagi jika lamaran sebelumnya telah ditolak.')
                    ->danger()
                    ->send();
                $this->halt();
            }

            // Jika status Diterima, cek tanggal selesai magang
            if ($lamaran->status === 'Diterima' && $lamaran->lowongan) {
                $tanggalSelesai = $lamaran->lowongan->tanggal_selesai_magang ?? null;
                if ($tanggalSelesai && now()->lessThanOrEqualTo($tanggalSelesai)) {
                    Notification::make()
                        ->title('Gagal Mengajukan Lamaran')
                        ->body('Anda masih memiliki magang aktif yang belum selesai. Anda hanya bisa melamar lagi jika periode magang sudah berakhir atau lamaran sebelumnya ditolak.')
                        ->danger()
                        ->send();
                    $this->halt();
                }
            }
        }
    }


    protected function afterMount(): void
    {
        $request = app(Request::class);
        $lowongan_id = $request->query('id_lowongan');

        if ($lowongan_id) {
            $this->evaluate();
        }
    }

    protected function evaluate(): void
    {
        $state = $this->form->getState();

        $this->form->fill($state);
    }
}
