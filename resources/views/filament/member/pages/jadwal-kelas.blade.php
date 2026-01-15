<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach($this->getSchedules() as $schedule)
            @php
                $startTime = \Carbon\Carbon::parse($schedule->start_time);
                $endTime = \Carbon\Carbon::parse($schedule->end_time);
                $isCurrentlyLive = now()->format('l') === $schedule->day && now()->between($startTime, $endTime);
            @endphp
            
            <x-filament::section
                @class([
                    'fi-section-live' => $isCurrentlyLive,
                    'transition duration-300 hover:shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10'
                ])
            >
                <x-slot name="heading">
                    <div class="flex flex-col gap-y-1">
                        <span class="text-xl font-black text-primary-600 dark:text-primary-500 tracking-tight leading-tight uppercase">
                            {{ $schedule->division->name }}
                        </span>
                        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                            {{ $schedule->day }}
                        </span>
                    </div>
                </x-slot>

                <x-slot name="headerEnd">
                    @if($isCurrentlyLive)
                        <div class="flex items-center gap-x-2">
                             <div class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                            </div>
                            <x-filament::badge color="primary" size="sm" class="font-bold">
                                NOW
                            </x-filament::badge>
                        </div>
                    @endif
                </x-slot>

                <div class="space-y-8 pt-6 pb-4">
                    {{-- Row: Room --}}
                    <div class="flex items-center gap-x-5">
                        <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 ring-1 ring-primary-500/20">
                            <x-heroicon-s-map-pin class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400 tracking-tight leading-none mb-1.5">Room</span>
                            <span class="text-xl font-black text-gray-950 dark:text-white leading-tight">
                                {{ $schedule->classroom ?? 'Lab PPLG' }}
                            </span>
                        </div>
                    </div>

                    {{-- Row: Time --}}
                    <div class="flex items-center gap-x-5">
                        <div class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 ring-1 ring-primary-500/20">
                            <x-heroicon-s-clock class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-500 dark:text-gray-400 tracking-tight leading-none mb-1.5">Time</span>
                            <span class="text-xl font-black text-gray-950 dark:text-white leading-tight">
                                {{ $startTime->format('H:i') }} - {{ $endTime->format('H:i') }}
                            </span>
                        </div>
                    </div>
                </div>

                <x-slot name="footer">
                    <div class="w-full">
                        {{ ($this->absenAction)(['schedule_id' => $schedule->id]) }}
                    </div>
                </x-slot>
            </x-filament::section>
        @endforeach
    </div>

    @if($this->getSchedules()->isEmpty())
        <x-filament::section class="flex flex-col items-center justify-center py-16 text-center border-dashed">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mb-4">
                <x-filament::icon
                    icon="heroicon-o-calendar"
                    class="h-8 w-8 text-gray-300 dark:text-gray-600"
                />
            </div>
            <h3 class="text-xl font-black text-gray-950 dark:text-white tracking-tight">No Schedules</h3>
            <p class="mt-1 text-sm text-gray-500 max-w-xs mx-auto font-medium">You are not registered in any division schedule today.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
