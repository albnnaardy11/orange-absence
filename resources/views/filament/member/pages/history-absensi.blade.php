<x-filament-panels::page>
    <div class="mb-8">
        @foreach($schedules as $schedule)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-4 mb-8 last:mb-0">
                
                {{-- Card 1: Division (Spans Full) --}}
                <x-filament::section class="col-span-full">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-blue-100 rounded-full dark:bg-blue-900/30">
                            <x-filament::icon icon="heroicon-o-book-open" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Division</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white uppercase">{{ $schedule->division->name }}</h3>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Card 2: Check-in Time --}}
                <x-filament::section>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-amber-100 rounded-full dark:bg-amber-900/30">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Check-in Time</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</h3>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Card 3: Check-out Time --}}
                <x-filament::section>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-rose-100 rounded-full dark:bg-rose-900/30">
                            <x-filament::icon icon="heroicon-o-clock" class="h-6 w-6 text-rose-600 dark:text-rose-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Check-out Time</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</h3>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Card 4: Room --}}
                <x-filament::section>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-orange-100 rounded-full dark:bg-orange-900/30">
                            <x-filament::icon icon="heroicon-o-home" class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Room</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $schedule->classroom ?? '-' }}</h3>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Card 5: Day --}}
                <x-filament::section>
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-indigo-100 rounded-full dark:bg-indigo-900/30">
                            <x-filament::icon icon="heroicon-o-calendar" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Day</p>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $schedule->day }}</h3>
                        </div>
                    </div>
                </x-filament::section>

            </div>
        @endforeach

        @if($schedules->isEmpty())
            <x-filament::section class="text-center py-6">
                <p class="text-gray-500">No active schedules found.</p>
            </x-filament::section>
        @endif
    </div>

    {{ $this->table }}
</x-filament-panels::page>