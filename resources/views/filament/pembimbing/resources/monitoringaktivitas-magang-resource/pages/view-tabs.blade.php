<x-filament::section>
    <x-filament::grid>
        <div class="col-span-full space-y-4">
            <x-filament::card>
                <x-slot name="header">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                        Beri Feedback
                    </h2>
                </x-slot>

                {{ $form }}

                <x-slot name="footer">
                    <div class="flex items-center gap-3">
                        <x-filament::button wire:click="save" color="primary" icon="heroicon-m-check-badge">
                            Simpan Feedback
                        </x-filament::button>

                        <x-filament::button color="gray" icon="heroicon-m-x-mark" tag="a" :href="url()->previous()">
                            Batalkan
                        </x-filament::button>
                    </div>
                </x-slot>
            </x-filament::card>
        </div>
    </x-filament::grid>
</x-filament::section>
