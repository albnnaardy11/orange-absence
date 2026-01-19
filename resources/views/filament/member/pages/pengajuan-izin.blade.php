<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}
        
        <x-filament::button type="submit" class="mt-8" size="lg">
            Kirim Pengajuan
        </x-filament::button>
    </form>
</x-filament-panels::page>
