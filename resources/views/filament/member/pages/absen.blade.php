<x-filament-panels::page>
    <div class="max-w-xl mx-auto">
        <x-filament::section>
            <x-slot name="heading">
                Input Attendance Code
            </x-slot>

            <form wire:submit="submit" class="space-y-6">
                {{ $this->form }}

                <x-filament::button type="submit" class="w-full py-4 text-lg">
                    Check-in Now
                </x-filament::button>
            </form>
        </x-filament::section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
