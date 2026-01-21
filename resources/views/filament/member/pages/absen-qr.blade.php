<x-filament-panels::page>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <div class="max-w-full flex flex-col items-center justify-center space-y-8 py-10">
        
        <x-filament::section class="w-full max-w-lg">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-orange-600 dark:text-orange-500">Scan QR Absensi</h2>
                <p class="text-gray-500 text-sm">Cukup scan kode QR untuk langsung melakukan absensi</p>
            </div>

            <div class="flex flex-col items-center justify-center">
                <div id="reader" class="w-full max-w-sm rounded-2xl overflow-hidden border-4 border-orange-500 bg-black shadow-2xl"></div>
                
                <div id="scan-result" class="hidden mt-6 p-4 bg-green-500 text-white font-bold rounded-xl w-full text-center animate-bounce">
                     QR Terdeteksi! Memproses...
                </div>

                <div id="ssl-warning" class="hidden mt-4 p-2 bg-red-100 text-red-800 text-xs rounded-lg text-center">
                     Kamera & GPS membutuhkan HTTPS.
                </div>

                <x-filament::button id="start-scan-btn" class="mt-8 w-full py-4 text-lg shadow-lg" color="warning" icon="heroicon-o-camera">
                    Mulai Kamera / Scan
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Hidden form, needed for state management but not shown --}}
        <div class="hidden">
            {{ $this->form }}
        </div>
    </div>

    {{-- GPS Status --}}
    <div id="gps-status" class="fixed top-20 right-4 z-50 flex items-center gap-x-2 px-3 py-1.5 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 opacity-0 transition-opacity duration-500">
        <div class="h-2 w-2 rounded-full bg-red-500 animate-pulse" id="gps-dot"></div>
        <span class="text-xs font-bold text-gray-700 dark:text-gray-300" id="gps-text">Mencari GPS...</span>
    </div>

    {{-- Scanner Overlay Style --}}
    <style>
        #reader { border: none !important; }
        #reader video { object-fit: cover; border-radius: 1rem; }
        .scan-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            pointer-events: none;
            display: flex; align-items: center; justify-content: center;
        }
        .scan-box {
            width: 250px; height: 250px;
            border: 2px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 0 0 4000px rgba(0,0,0,0.5); /* Dim outside */
            border-radius: 20px;
            position: relative;
        }
        .scan-box::after {
            content: ''; position: absolute; top: -10px; left: -10px; right: -10px; bottom: -10px;
            border: 4px solid #f97316; /* Orange border */
            border-radius: 24px;
            clip-path: polygon(0 0, 0 40px, 40px 0, 100% 0, 100% 40px, calc(100% - 40px) 0, 100% 100%, 100% calc(100% - 40px), calc(100% - 40px) 100%, 0 100%, 0 calc(100% - 40px), 40px 100%);
            animation: pulse-border 2s infinite;
        }
        @keyframes pulse-border {
            0% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.02); }
            100% { opacity: 0.8; transform: scale(1); }
        }
    </style>

    <script>
        // --- GPS Logic ---
        window.userLat = null;
        window.userLong = null;

        function updateGPS() {
            if (!navigator.geolocation) {
                document.getElementById('gps-text').innerText = 'GPS Error';
                return;
            }
            navigator.geolocation.watchPosition(
                (pos) => {
                    window.userLat = pos.coords.latitude;
                    window.userLong = pos.coords.longitude;
                    
                    const el = document.getElementById('gps-status');
                    const dot = document.getElementById('gps-dot');
                    const text = document.getElementById('gps-text');
                    
                    if (el) el.classList.remove('opacity-0');
                    if (dot) {
                         dot.classList.replace('bg-red-500', 'bg-green-500');
                         dot.classList.remove('animate-pulse');
                    }
                    if (text) text.innerText = 'GPS: Terkunci';
                    
                    // Sync to Livewire quietly
                    @this.set('user_lat', window.userLat, true); // defer
                    @this.set('user_long', window.userLong, true); // defer
                },
                (err) => {
                    console.error("GPS Error", err);
                },
                { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
            );
        }
        document.addEventListener('DOMContentLoaded', updateGPS);

        // --- SCANNER Logic ---
        let html5QrcodeScanner = null;
        let isScanning = false;
        let lastScanTime = 0;
        const DEBOUNCE_MS = 3000;

        async function onScanSuccess(decodedText) {
            const now = Date.now();
            if (isScanning || (now - lastScanTime < DEBOUNCE_MS)) {
                return;
            }

            isScanning = true;
            lastScanTime = now;
            console.log("QR Found:", decodedText);

            if (navigator.vibrate) navigator.vibrate(200);

            const resDiv = document.getElementById('scan-result');
            if (resDiv) {
                resDiv.innerHTML = "🔄 Memproses...";
                resDiv.classList.remove('hidden', 'bg-green-500', 'bg-red-500');
                resDiv.classList.add('bg-blue-500', 'text-white');
            }

            if (html5QrcodeScanner) html5QrcodeScanner.pause();

            try {
                // Set Properties First (Robust method)
                console.log("Setting Livewire properties...");
                
                // Ensure GPS is set if available
                if (window.userLat && window.userLong) {
                    await @this.set('user_lat', window.userLat);
                    await @this.set('user_long', window.userLong);
                } else {
                    console.warn("GPS not ready yet, sending nulls.");
                }

                await @this.set('qr_payload', decodedText);
                
                // Trigger Action
                console.log("Calling saveAttendance...");
                await @this.call('saveAttendance');
                
            } catch (error) {
                console.error("Javascript Error during submission:", error);
                if (resDiv) {
                    resDiv.innerHTML = "❌ Gagal: " + error;
                    resDiv.classList.remove('bg-blue-500');
                    resDiv.classList.add('bg-red-500');
                }
            } finally {
                // We rely on Livewire events 'attendance-success' or 'attendance-failure' 
                // to update UI further, but we reset the flag here just in case.
                 setTimeout(() => { 
                    isScanning = false; 
                    if (html5QrcodeScanner) html5QrcodeScanner.resume();
                }, DEBOUNCE_MS);
            }
        }

        // Listen for backend events
        window.addEventListener('attendance-success', event => {
            const resDiv = document.getElementById('scan-result');
            if (resDiv) {
                resDiv.innerHTML = "✅ Berhasil!";
                resDiv.classList.remove('bg-blue-500', 'bg-red-500');
                resDiv.classList.add('bg-green-500');
                setTimeout(() => { resDiv.classList.add('hidden'); }, 4000);
            }
        });

        window.addEventListener('attendance-failure', event => {
             const resDiv = document.getElementById('scan-result');
             if (resDiv) {
                resDiv.innerHTML = "❌ Gagal: " + (event.detail.error || 'Server Error');
                resDiv.classList.add('bg-red-500');
             }
        });

        document.getElementById('start-scan-btn').addEventListener('click', () => {
            document.getElementById('scan-result').classList.add('hidden');
            
            // Re-init if needed
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.clear();
                } catch(e) {}
            }
            
            startScanner();
        });

        function startScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 30, // High FPS (Target 30-60)
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    // Advanced Camera Config
                    videoConstraints: {
                        width: { min: 640, ideal: 1920, max: 1920 },
                        height: { min: 480, ideal: 1080, max: 1080 },
                        facingMode: "environment", // Backend camera
                        focusMode: "continuous" // Attempt to force focus
                    },
                    formatsToSupport: [ Html5QrcodeSupportedFormats.QR_CODE ], // Optimized for QR
                },
                /* verbose= */ false
            );
            
            // Customize the render to remove default overlay if possible, 
            // but Html5QrcodeScanner is high-level. 
            // We use CSS to make it look better.
            html5QrcodeScanner.render(onScanSuccess, (err) => { /* ignore frame errors */ });
        }
    </script>
</x-filament-panels::page>
