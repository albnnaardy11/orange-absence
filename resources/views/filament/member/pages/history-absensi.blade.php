<x-filament-panels::page>
    <div class="mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            {{-- Card 1: Name --}}
            <x-filament::section>
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex-shrink-0 p-2.5 sm:p-3 bg-blue-100 rounded-full dark:bg-blue-900/30">
                        <x-filament::icon icon="heroicon-o-user" class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Nama Lengkap</p>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white uppercase truncate">{{ auth()->user()->name }}</h3>
                    </div>
                </div>
            </x-filament::section>

            {{-- Card 2: Email --}}
            <x-filament::section>
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex-shrink-0 p-2.5 sm:p-3 bg-amber-100 rounded-full dark:bg-amber-900/30">
                        <x-filament::icon icon="heroicon-o-envelope" class="h-5 w-5 sm:h-6 sm:w-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Email</p>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->email }}</h3>
                    </div>
                </div>
            </x-filament::section>

            {{-- Card 3: Divisions --}}
            <x-filament::section class="sm:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="flex-shrink-0 p-2.5 sm:p-3 bg-indigo-100 rounded-full dark:bg-indigo-900/30">
                        <x-filament::icon icon="heroicon-o-academic-cap" class="h-5 w-5 sm:h-6 sm:w-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wide">Divisi Saya</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @forelse(auth()->user()->divisions as $division)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300 uppercase">
                                    {{ $division->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">Belum bergabung divisi</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>