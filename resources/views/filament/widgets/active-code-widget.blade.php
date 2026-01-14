<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
             <h2 class="text-xl font-bold mb-2 text-primary-600">Live Verification Code</h2>
             @foreach($codes as $code)
             <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg dark:bg-gray-800">
                 <div>
                     <h3 class="text-lg font-medium text-gray-500">{{ $code->division->name }}</h3>
                     <p class="text-4xl font-bold tracking-tight text-primary-600 dark:text-primary-400 mt-1">
                         {{ $code->code }}
                     </p>
                     <p class="text-sm text-gray-400">Expires: {{ $code->expires_at?->format('H:i') ?? '--:--' }}</p>
                 </div>
                 <x-filament::button
                     icon="heroicon-o-clipboard"
                     color="gray"
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
                <p class="text-center text-gray-500">No active codes for today.</p>
             @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
