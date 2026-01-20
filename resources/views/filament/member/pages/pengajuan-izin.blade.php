<x-filament-panels::page>
    <div class="max-w-full">
        <x-filament::section>
            <form wire:submit="create" class="space-y-6">
                {{ $this->form }}

                <div class="mt-8">
                    <x-filament::button type="submit" size="xl" class="w-full">
                        Kirim Pengajuan
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
