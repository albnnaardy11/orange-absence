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

    {{-- GPS Status Indicator (Positioned under the profile avatar) --}}
    <div id="gps-status" class="fixed top-[5.75rem] right-4 flex items-center gap-x-2 px-3 py-1.5 rounded-full bg-white dark:bg-gray-900 shadow-lg opacity-0 transition-all duration-500 transform translate-y-[-10px]">
        <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.5)]" id="gps-dot"></div>
        <span class="text-[10px] font-black text-gray-600 dark:text-gray-300 tracking-[0.1em] whitespace-nowrap" id="gps-text">SYNCING</span>
        <div class="w-px h-3 bg-gray-200 dark:bg-white/20 mx-1"></div>
        <button onclick="updateGPS(true)" class="p-1.5 hover:bg-gray-100 dark:hover:bg-white/5 rounded-full transition-colors group" title="Refresh Lokasi">
            <x-filament::icon icon="heroicon-o-arrow-path" class="w-3.5 h-3.5 text-gray-400 group-hover:text-orange-500 transition-colors" />
        </button>
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
            el.classList.remove('opacity-0', 'translate-y-[-10px]');
            el.classList.add('opacity-100', 'translate-y-0');

            if (status === 'locked') {
                dot.classList.remove('bg-red-500');
                dot.classList.add('bg-green-500');
                dot.classList.remove('animate-pulse');
                dot.classList.add('shadow-[0_0_8px_#22c55e]');
                text.innerText = 'GPS ON';
            } else if (status === 'error') {
                dot.classList.add('bg-red-500');
                text.innerText = 'GPS OFF';
            }
        }

        function initGPS() {
            if (!navigator.geolocation) {
                updateGPSUI('error');
                return;
            }

            navigator.geolocation.watchPosition(
                function(position) {
                    window.userLat = position.coords.latitude;
                    window.userLong = position.coords.longitude;
                    
                    // Save to local persistence
                    setCookie('user_lat', window.userLat);
                    setCookie('user_long', window.userLong);
                    
                    updateGPSUI('locked');
                    broadcastGPS();
                },
                function(error) {
                    console.error('GPS Error:', error.message);
                    updateGPSUI('error');
                },
                { 
                    enableHighAccuracy: true, 
                    maximumAge: 5000, 
                    timeout: 10000 
                }
            );
        }

        function broadcastGPS() {
            if (!window.userLat || !window.userLong) return;
            
            window.dispatchEvent(new CustomEvent('gps-updated', { 
                detail: { lat: window.userLat, long: window.userLong } 
            }));

            // Direct injection into any available inputs
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

        function updateGPS(force = false) {
            if (force) location.reload();
            else initGPS();
        }

        // Start immediately when script loads
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGPS);
        } else {
            // DOM already loaded, start immediately
            initGPS();
        }

        // Also start on Livewire navigation (for SPA mode)
        document.addEventListener('livewire:navigated', initGPS);

        // Re-inject when modal opens
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.addedNodes.length) {
                    setTimeout(broadcastGPS, 100);
                }
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });

        document.addEventListener('mousedown', function(e) {
            if (e.target.innerText && e.target.innerText.includes('Check-in')) {
                broadcastGPS();
            }
        });
    </script>
</x-filament-panels::page>
