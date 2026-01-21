<div>
@php
    $activeSchedule = $this->getActiveSchedule();
@endphp

@if($activeSchedule)
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 sm:gap-6 py-2">
            <div class="flex items-start sm:items-center gap-x-3 sm:gap-x-6 w-full md:w-auto">
                <div class="flex-shrink-0 relative">
                    <div class="p-3 sm:p-4 bg-primary-100 rounded-2xl dark:bg-primary-900/30">
                        <x-filament::icon
                            icon="heroicon-o-academic-cap"
                            class="h-8 w-8 sm:h-10 sm:w-10 text-primary-600 dark:text-primary-400"
                        />
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-3 w-3 sm:h-4 sm:w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 sm:h-4 sm:w-4 bg-primary-500"></span>
                    </span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-x-2 flex-wrap">
                        <x-filament::badge color="primary" size="sm" class="font-bold">LIVE NOW</x-filament::badge>
                        <span class="text-xs sm:text-sm font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest truncate">{{ $activeSchedule->division->name }}</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-950 dark:text-white tracking-tight leading-tight mt-1 truncate">
                        {{ $activeSchedule->classroom ?? 'Classroom' }}
                    </h2>
                    <p class="text-sm sm:text-base md:text-lg font-medium text-gray-500">
                        Time: {{ \Carbon\Carbon::parse($activeSchedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activeSchedule->end_time)->format('H:i') }}
                    </p>
                </div>
            </div>
            
            <div class="w-full md:w-auto">
                <x-filament::button
                    size="xl"
                    icon="heroicon-m-check-circle"
                    tag="a"
                    href="{{ url('/member/jadwal-kelas') }}"
                    class="w-full md:w-auto px-6 sm:px-8 md:px-10 py-3 sm:py-4 font-black text-base sm:text-lg md:text-xl shadow-lg shadow-primary-500/20 min-h-[48px]"
                >
                    CHECK-IN NOW
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
@endif
</div>

