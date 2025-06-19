{{-- resources/views/filament/widgets/preferensi-warning.blade.php --}}
<x-filament::card class="bg-warning-100 border-l-4 border-warning-500 text-warning-700 p-4">
    <h2 class="text-lg font-semibold">⚠️ Preferensi Belum Diisi!</h2>
    <p class="text-sm mt-1">
        Anda harus mengisi preferensi terlebih dahulu agar dapat menggunakan semua fitur sistem dengan optimal.
    </p><br>

    <div class="mt-4">
        <x-filament::button 
            tag="a"
            href="{{ route('filament.mahasiswa.resources.preferensi-mahasiswas.create') }}"
            color="primary"
            rounded
        >
            Buat Preferensi Profil Baru
        </x-filament::button>
    </div>
</x-filament::card>