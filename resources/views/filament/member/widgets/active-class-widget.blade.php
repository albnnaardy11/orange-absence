<div>
@php
    $activeSchedule = $this->getActiveSchedule();
@endphp

@if($activeSchedule)
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 py-2">
            <div class="flex items-center gap-x-6">
                <div class="flex-shrink-0 relative">
                    <div class="p-4 bg-primary-100 rounded-2xl dark:bg-primary-900/30">
                        <x-filament::icon
                            icon="heroicon-o-academic-cap"
                            class="h-10 w-10 text-primary-600 dark:text-primary-400"
                        />
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-primary-500"></span>
                    </span>
                </div>
                <div>
                    <div class="flex items-center gap-x-2">
                        <x-filament::badge color="primary" size="sm" class="font-bold">LIVE NOW</x-filament::badge>
                        <span class="text-sm font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest">{{ $activeSchedule->division->name }}</span>
                    </div>
                    <h2 class="text-3xl font-black text-gray-950 dark:text-white tracking-tight leading-tight mt-1">
                        {{ $activeSchedule->classroom ?? 'Classroom' }}
                    </h2>
                    <p class="text-lg font-medium text-gray-500">
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
                    class="w-full md:w-auto px-10 py-4 font-black text-xl shadow-lg shadow-primary-500/20"
                >
                    CHECK-IN NOW
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
@endif
</div>

