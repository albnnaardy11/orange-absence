<x-filament-panels::page>
    <div class="max-w-2xl">
        <x-filament::section>
            <form wire:submit="create" class="space-y-6">
                {{ $this->form }}

                <div class="mt-12 pt-6">
                    <x-filament::button type="submit" size="lg" class="w-full sm:w-auto">
                        Kirim Pengajuan
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
