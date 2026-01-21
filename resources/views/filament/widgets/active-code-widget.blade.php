<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-3 sm:gap-4">
             <h2 class="text-lg sm:text-xl font-bold mb-1 sm:mb-2 text-primary-600">Live Verification Code</h2>
             @foreach($codes as $code)
             <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 p-3 sm:p-4 bg-gray-100 rounded-lg dark:bg-gray-800">
                 <div class="w-full sm:w-auto">
                     <h3 class="text-base sm:text-lg font-medium text-gray-500">{{ $code->division->name }}</h3>
                     <p class="text-3xl sm:text-4xl font-bold tracking-tight text-primary-600 dark:text-primary-400 mt-1">
                         {{ $code->code }}
                     </p>
                     <p class="text-xs sm:text-sm text-gray-400 mt-1">Expires: {{ $code->expires_at?->format('H:i') ?? '--:--' }}</p>
                 </div>
                 <x-filament::button
                     icon="heroicon-o-clipboard"
                     color="gray"
                     size="sm"
                     class="w-full sm:w-auto min-h-[44px]"
                     x-data="{ 
                        text: '{{ $code->code }}',
                        copy() {
                            window.navigator.clipboard.writeText(this.text);
                            new FilamentNotification()
                                .title('Copied!')
                                .success()
                                .send();
                        }
                     }"
                     x-on:click="copy"
                 >
                     Copy
                 </x-filament::button>
             </div>
             @endforeach

             @if($codes->isEmpty())
                <p class="text-center text-sm sm:text-base text-gray-500 py-4">No active codes for today.</p>
             @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
