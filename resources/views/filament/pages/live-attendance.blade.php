<x-filament-panels::page>
    <div class="flex justify-center py-4" x-data="{ 
        timer: 45, 
        init() { 
            setInterval(() => {
                if (this.timer > 0) this.timer--;
            }, 1000);
        }
    }" @qr-refreshed.window="timer = 45">
        
        <x-filament::section class="w-full max-w-xl">
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-m-qr-code" class="mr-2 w-5 h-5 text-orange-500" />
                    <span>Live Attendance Broadcaster</span>
                </div>
            </x-slot>

            <div class="flex flex-col gap-8 py-4">
                {{-- Integrated Selection Control --}}
                <div class="border-b border-gray-100 dark:border-white/5 pb-6">
                    {{ $this->form }}
                </div>

                @if($qrCode)
                <div class="flex flex-col items-center gap-6 animate-in fade-in zoom-in duration-500" wire:poll.45s="refreshQr">
                    
                    {{-- Minimalist Header --}}
                    <div class="text-center space-y-1 mt-6">
                        @if($active_code)
                        <div class="flex flex-col items-center mb-4">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em] mb-1">Session Code</span>
                            <div class="text-5xl font-black text-orange-600 tracking-widest font-mono drop-shadow-sm">
                                {{ $active_code }}
                            </div>
                        </div>
                        @endif
                        <h3 class="text-2xl font-black tracking-tighter uppercase italic drop-shadow-sm opacity-50">Scan QR Code</h3>
                    </div>

                    {{-- QR Frame with improved spacing and rounding --}}
                    <div class="relative p-8 my-4 bg-white dark:bg-white rounded-[2.5rem] shadow-sm ring-1 ring-gray-950/5">
                        <div class="qr-unified overflow-hidden rounded-2xl">
                            {!! $qrCode !!}
                        </div>
                    </div>

                    {{-- Bottom Information --}}
                    <div class="w-full flex flex-col items-center gap-4">
                        <div class="flex items-center gap-3 px-4 py-2 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-white/5">
                            <x-filament::icon icon="heroicon-o-shield-check" class="w-4 h-4 text-orange-500" />
                            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                                Valid until <span class="text-gray-900 dark:text-white font-black" x-text="timer + 's'"></span>
                            </span>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-40 h-1 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-orange-500 transition-all duration-1000 ease-linear shadow-[0_0_8px_rgba(249,115,22,0.4)]" 
                                 :style="'width: ' + (timer/45*100) + '%'"></div>
                        </div>
                    </div>
                </div>
                @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="p-4 bg-orange-50 dark:bg-orange-500/10 rounded-full mb-4">
                        <x-filament::icon icon="heroicon-o-cursor-arrow-ripple" class="w-10 h-10 text-orange-500" />
                    </div>
                    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Select a division to start</p>
                </div>
                @endif
            </div>
        </x-filament::section>

    </div>

    <style>
        .qr-unified svg {
            width: 320px !important;
            height: 320px !important;
            display: block;
        }
    </style>
</x-filament-panels::page>
