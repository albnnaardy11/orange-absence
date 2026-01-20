<x-filament-panels::page>
    <div class="max-w-full">
        <x-filament::section>
            <x-slot name="heading">
                Input Attendance Code
            </x-slot>

            <form wire:submit="submit" class="space-y-6">
                {{ $this->form }}

                <x-filament::button type="submit" class="w-full py-4 text-lg">
                    Check-in Now
                </x-filament::button>
            </form>
        </x-filament::section>
    </div>

    <x-filament-actions::modals />

    {{-- GPS Status Indicator --}}
    <div id="gps-status" class="fixed top-18 right-4 z-50 flex items-center gap-x-2 px-3 py-1.5 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-500 opacity-0 translate-y-[-20px]">
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

        function updateCoords() {
            if (!navigator.geolocation) return;

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    window.userLat = position.coords.latitude;
                    window.userLong = position.coords.longitude;
                    
                    setCookie('user_lat', window.userLat);
                    setCookie('user_long', window.userLong);

                    updateGPSUI('locked');
                    
                    const latInput = document.getElementById('user_lat');
                    const longInput = document.getElementById('user_long');
                    
                    if (latInput) {
                        latInput.value = window.userLat;
                        latInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    if (longInput) {
                        longInput.value = window.userLong;
                        longInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                },
                function(error) {
                    console.error('GPS Error:', error.message);
                    updateGPSUI('error');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        document.addEventListener('DOMContentLoaded', updateCoords);
        document.addEventListener('click', () => {
             if (!window.userLat) updateCoords();
        });
        setInterval(updateCoords, 20000);
    </script>
</x-filament-panels::page>
