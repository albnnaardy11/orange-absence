<x-filament-panels::page>
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Card 1: Name --}}
            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-100 rounded-full dark:bg-blue-900/30">
                        <x-filament::icon icon="heroicon-o-user" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Nama Lengkap</p>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase truncate">{{ auth()->user()->name }}</h3>
                    </div>
                </div>
            </x-filament::section>

            {{-- Card 2: Email --}}
            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-amber-100 rounded-full dark:bg-amber-900/30">
                        <x-filament::icon icon="heroicon-o-envelope" class="h-6 w-6 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="truncate">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Email</p>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->email }}</h3>
                    </div>
                </div>
            </x-filament::section>

            {{-- Card 3: Divisions --}}
            <x-filament::section>
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-indigo-100 rounded-full dark:bg-indigo-900/30">
                        <x-filament::icon icon="heroicon-o-academic-cap" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Divisi Saya</p>
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