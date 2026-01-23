<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-key" icon-color="primary">
        <x-slot name="heading">
            Live Session Codes
        </x-slot>

        <div class="grid grid-cols-1 gap-3" wire:poll.60s>
            @forelse($codes as $code)
                <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-white/5 group transition-all hover:bg-white dark:hover:bg-white/10 hover:shadow-sm">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest break-all">
                            {{ $code->division->name }}
                        </span>
                        <div class="flex items-center gap-3">
                            <span class="text-3xl font-black text-primary-600 dark:text-primary-500 font-mono tracking-tighter">
                                {{ $code->code }}
                            </span>
                            <button 
                                x-on:click="window.navigator.clipboard.writeText('{{ $code->code }}'); new FilamentNotification().title('Sesi Code Berhasil di Copy!').success().send()"
                                class="p-1 text-gray-400 hover:text-primary-500 transition-colors"
                            >
                                <x-filament::icon icon="heroicon-o-clipboard-document-check" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end gap-2">
                        <x-filament::badge color="success" size="sm" icon="heroicon-m-bolt" class="font-bold">
                            ACTIVE
                        </x-filament::badge>
                        <span class="text-[10px] text-gray-400 font-medium">
                            Ends: {{ $code->expires_at?->format('H:i') ?? '--:--' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-6 text-center opacity-40">
                    <x-filament::icon icon="heroicon-o-clock" class="w-10 h-10 mb-2 text-gray-300" />
                    <p class="text-xs font-bold uppercase tracking-widest">No Active Sessions</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
