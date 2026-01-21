<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
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
                        <span class="text-lg sm:text-xl font-black text-primary-600 dark:text-primary-500 tracking-tight leading-tight uppercase">
                            {{ $schedule->division->name }}
                        </span>
                        <span class="text-xs sm:text-sm font-semibold text-gray-500 dark:text-gray-400">
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
                    <div class="flex items-center gap-x-3 sm:gap-x-5">
                        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 ring-1 ring-primary-500/20">
                            <x-heroicon-s-map-pin class="h-5 w-5 sm:h-6 sm:w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="text-xs sm:text-sm font-bold text-gray-500 dark:text-gray-400 tracking-tight leading-none mb-1 sm:mb-1.5">Room</span>
                            <span class="text-base sm:text-xl font-black text-gray-950 dark:text-white leading-tight truncate">
                                {{ $schedule->classroom ?? 'Lab PPLG' }}
                            </span>
                        </div>
                    </div>

                    {{-- Row: Time --}}
                    <div class="flex items-center gap-x-3 sm:gap-x-5">
                        <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-primary-100 dark:bg-primary-900/30 ring-1 ring-primary-500/20">
                            <x-heroicon-s-clock class="h-5 w-5 sm:h-6 sm:w-6 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div class="flex flex-col min-w-0 flex-1">
                            <span class="text-xs sm:text-sm font-bold text-gray-500 dark:text-gray-400 tracking-tight leading-none mb-1 sm:mb-1.5">Time</span>
                            <span class="text-base sm:text-xl font-black text-gray-950 dark:text-white leading-tight">
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
        <x-filament::section class="border-dashed py-8 sm:py-12">
            <div class="flex flex-col items-center justify-center text-center px-4">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3 sm:mb-4 mx-auto shadow-inner ring-1 ring-gray-950/5 dark:ring-white/10">
                    <x-filament::icon
                        icon="heroicon-o-calendar"
                        class="h-7 w-7 sm:h-8 sm:w-8 text-gray-400 dark:text-gray-500"
                    />
                </div>
                <h3 class="text-lg sm:text-xl font-black text-gray-950 dark:text-white tracking-tight">No Schedules</h3>
                <p class="mt-2 text-xs sm:text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto font-medium">You are not registered in any division schedule today.</p>
            </div>
        </x-filament::section>
    @endif

    {{-- GPS Status Indicator --}}
    <div id="gps-status" class="fixed top-16 sm:top-18 right-2 sm:right-4 z-50 flex items-center gap-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-500 opacity-0 translate-y-[-20px]">
        <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse" id="gps-dot"></div>
        <span class="text-xs font-bold text-gray-600 dark:text-gray-300" id="gps-text">Mencari GPS...</span>
    </div>

    <script>
        window.userLat = null;
        window.userLong = null;

        function setCookie(name, value) {
            document.cookie = name + "=" + value + "; path=/; max-age=3600; SameSite=Lax";
        }

        function updateGPSUI(status) {
            const el = document.getElementById('gps-status');
            const dot = document.getElementById('gps-dot');
            const text = document.getElementById('gps-text');
            
            if (!el) return;
            el.classList.remove('opacity-0', 'translate-y-[-20px]');
            el.classList.add('opacity-100', 'translate-y-0');

            if (status === 'locked') {
                dot.classList.remove('bg-red-500');
                dot.classList.add('bg-green-500');
                dot.classList.remove('animate-pulse');
                text.innerText = 'GPS: OK';
            } else if (status === 'error') {
                dot.classList.add('bg-red-500');
                text.innerText = 'GPS: Error';
            }
        }

        // Monitoring
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                function(position) {
                    window.userLat = position.coords.latitude;
                    window.userLong = position.coords.longitude;
                    
                    // Bulletproof: Save to Cookies
                    setCookie('user_lat', window.userLat);
                    setCookie('user_long', window.userLong);

                    updateGPSUI('locked');
                    injectCoords();
                },
                function(error) {
                    console.error('GPS Error:', error.message);
                    updateGPSUI('error');
                },
                { enableHighAccuracy: true, maximumAge: 0 }
            );
        }

        function injectCoords() {
            if (!window.userLat || !window.userLong) return;
            
            window.dispatchEvent(new CustomEvent('gps-updated', { 
                detail: { lat: window.userLat, long: window.userLong } 
            }));

            document.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name') || '';
                const id = input.id || '';
                if (name.includes('user_lat') || id === 'user_lat') {
                    input.value = window.userLat;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
                if (name.includes('user_long') || id === 'user_long') {
                    input.value = window.userLong;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) injectCoords();
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });

        document.addEventListener('mousedown', function(e) {
            if (e.target.innerText && e.target.innerText.includes('Check-in')) {
                injectCoords();
            }
        });
    </script>
</x-filament-panels::page>
