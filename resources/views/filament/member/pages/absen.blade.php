<x-filament-panels::page>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <div class="max-w-full space-y-6" x-data="{ mode: 'qr' }">
        
        {{-- Mode Switcher --}}
        <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
            <button @click="mode = 'qr'" wire:click="$set('mode', 'qr')"
                :class="mode === 'qr' ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                class="px-4 sm:px-6 py-3 rounded-lg font-bold text-base sm:text-lg shadow transition-all w-full sm:w-1/2 min-h-[48px]">
                📷 Scan QR Absen
            </button>
            <button @click="mode = 'manual'" wire:click="$set('mode', 'manual')"
                :class="mode === 'manual' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
                class="px-4 sm:px-6 py-3 rounded-lg font-bold text-base sm:text-lg shadow transition-all w-full sm:w-1/2 min-h-[48px]">
                ⌨️ Input Code Manual
            </button>
        </div>

        {{-- Scanner Section (Active only in QR Mode) --}}
        <div x-show="mode === 'qr'" x-transition>
            <x-filament::section>
                <x-slot name="heading">
                    Step 1: Scan QR Code
                </x-slot>

                <div class="flex flex-col items-center justify-center px-2 sm:px-0">
                    <div id="reader" class="w-full max-w-xs sm:max-w-sm rounded-lg overflow-hidden border-2 border-gray-300 dark:border-gray-700 bg-black"></div>
                    
                    <div id="scan-result" class="hidden mt-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg w-full max-w-xs sm:max-w-sm text-center">
                        ✅ QR Code Scanned! Processing...
                    </div>
                    
                    <div id="processing-indicator" class="hidden mt-4 flex flex-col items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg w-full max-w-xs sm:max-w-sm">
                        <svg class="animate-spin h-8 w-8 text-blue-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-blue-600 dark:text-blue-300 font-semibold">Memproses Absensi...</span>
                    </div>

                    <div id="scan-error-msg" class="hidden mt-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-sm rounded-lg text-center max-w-xs sm:max-w-sm">
                    </div>

                    <div id="ssl-warning" class="hidden mt-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 text-xs sm:text-sm rounded-lg text-center max-w-xs sm:max-w-sm">
                        ⚠️ Camera & GPS require HTTPS. Please check your connection security.
                    </div>

                    <x-filament::button id="start-scan-btn" class="mt-4 w-full max-w-xs sm:max-w-sm" color="warning" icon="heroicon-o-qr-code">
                        Start Camera / Rescan
                    </x-filament::button>
                </div>
            </x-filament::section>
        </div>

        {{-- Form Section --}}
        <x-filament::section>
            <x-slot name="heading">
                Step 2: Confirm & Submit
            </x-slot>

            {{-- Hidden GPS Inputs --}}
            <input type="hidden" wire:model="user_lat" id="user_lat">
            <input type="hidden" wire:model="user_long" id="user_long">

            <form wire:submit="submit" class="space-y-6">
                {{ $this->form }}

                <x-filament::button type="submit" class="w-full py-3 sm:py-4 text-base sm:text-lg min-h-[48px]">
                    <span x-show="mode === 'qr'">Check-in (QR)</span>
                    <span x-show="mode === 'manual'">Check-in (Manual)</span>
                </x-filament::button>
            </form>
        </x-filament::section>
    </div>

    <x-filament-actions::modals />

    {{-- GPS Status Indicator --}}
    <div id="gps-status" class="fixed top-16 sm:top-18 right-2 sm:right-4 z-50 flex items-center gap-x-2 px-2 sm:px-3 py-1 sm:py-1.5 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-500 opacity-0 translate-y-[-20px]">
        <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse" id="gps-dot"></div>
        <span class="text-xs font-bold text-gray-600 dark:text-gray-300" id="gps-text">Mencari GPS...</span>
    </div>

    <script>
        // GPS Logic
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
            if (!navigator.geolocation) {
                updateGPSUI('error');
                return;
            }

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
                    
                    // Also dispatch window event for Alpine widgets if any
                    window.dispatchEvent(new CustomEvent('gps-updated', { 
                        detail: { lat: window.userLat, long: window.userLong } 
                    }));
                },
                function(error) {
                    console.error('GPS Error:', error.message);
                    updateGPSUI('error');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                document.getElementById('ssl-warning').classList.remove('hidden');
            }
            updateCoords();
        });
        setInterval(updateCoords, 20000);

        // QR Scanner Logic
        let html5QrcodeScanner = null;

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`, decodedResult);
            
            // Stop scanning immediately on success
            if (html5QrcodeScanner) {
                 html5QrcodeScanner.clear();
            }

            document.getElementById('scan-result').classList.remove('hidden');
            document.getElementById('processing-indicator').classList.remove('hidden');

            // Direct call to backend with Payload AND GPS
            @this.handleQrScan(decodedText, window.userLat, window.userLong)
                .then(() => {
                    document.getElementById('processing-indicator').classList.add('hidden');
                })
                .catch(() => {
                    document.getElementById('processing-indicator').classList.add('hidden');
                });
        }

        function onScanError(errorMessage) {
            // handle error silently
        }

        document.getElementById('start-scan-btn').addEventListener('click', function() {
            document.getElementById('scan-result').classList.add('hidden');
            document.getElementById('processing-indicator').classList.add('hidden');
            document.getElementById('scan-error-msg').classList.add('hidden'); // Clear prev errors
            
            // Clear previous payload when starting scan
            @this.set('data.qr_payload', null);
            
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(() => { startScanner(); }).catch(err => { startScanner(); });
            } else {
                startScanner();
            }
        });

        function startScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", { 
                    fps: 30, // Faster scanning
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                }, 
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        }

        // Mode Switch Handler
        window.addEventListener('mode-changed', event => {
            // Use wire:click instead now for mode switching to ensure server sync
        });
    </script>
</x-filament-panels::page>
