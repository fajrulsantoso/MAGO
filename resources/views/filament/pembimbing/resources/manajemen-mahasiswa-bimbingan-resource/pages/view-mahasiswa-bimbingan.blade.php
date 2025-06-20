<x-filament::tabs>
    <x-filament::tabs.item label="Detail Aktivitas" name="detail" :active="true">
        <x-filament::section label="Informasi Aktivitas">
            <x-filament::grid :columns="2">
                <x-filament::card>
                    <x-filament::label>Mahasiswa</x-filament::label>
                    {{ $record->penempatan->mahasiswa->user->nama ?? '-' }}

                    <x-filament::label class="mt-4">Tanggal</x-filament::label>
                    {{ \Carbon\Carbon::parse($record->tanggal_log)->translatedFormat('d F Y') }}

                    <x-filament::label class="mt-4">Status Kehadiran</x-filament::label>
                    {{ ucfirst($record->status) }}
                </x-filament::card>

                <x-filament::card>
                    <x-filament::label>Perusahaan</x-filament::label>
                    {{ $record->penempatan->pengajuan->lowongan->perusahaan->nama ?? '-' }}

                    <x-filament::label class="mt-4">Aktivitas</x-filament::label>
                    {!! nl2br(e($record->keterangan)) !!}
                </x-filament::card>
            </x-filament::grid>
        </x-filament::section>
    </x-filament::tabs.item>

    <x-filament::tabs.item label="Feedback Pembimbing" name="feedback">
        <x-filament::section label="Beri / Edit Feedback">
            <x-filament::form wire:submit="{{ $submitAction }}">
                {{ $form }}
                <x-filament::button type="submit" color="success">
                    Simpan Feedback
                </x-filament::button>
            </x-filament::form>
        </x-filament::section>
    </x-filament::tabs.item>
</x-filament::tabs>
