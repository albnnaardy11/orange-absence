<x-filament-panels::page>
    <div class="flex flex-col items-center justify-center space-y-8" wire:poll.30s="refreshQr">
        
        <div class="w-full max-w-md">
            {{ $this->form }}
        </div>

        @if($division_id)
            <div class="p-8 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border-4 border-orange-500">
                <div class="text-center mb-4 text-gray-500 dark:text-gray-400 font-mono text-sm">
                    Refreshes automatically (30s)
                </div>
                
                <div class="flex justify-center">
                    {!! $qrCode !!}
                </div>

                <div class="text-center mt-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                        Scan to Attend
                    </h2>
                    <p class="text-gray-500">
                        Valid for {{ now()->format('d M Y') }}
                    </p>
                </div>
            </div>
            
            <div class="text-xs text-gray-400 font-mono">
                Secure Token: {{ substr($secret, 0, 8) }}...
            </div>
        @else
            <div class="text-center text-gray-500">
                Please select a division to generate QR Code.
            </div>
        @endif
    </div>
</x-filament-panels::page>
