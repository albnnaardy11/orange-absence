<div id="absen-root-wrapper">
    <x-filament-panels::page>
        <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
        
        <div class="max-w-full flex flex-col items-center justify-center space-y-8 py-10">
            
            <x-filament::section class="w-full max-w-lg">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-orange-600 dark:text-orange-500">Scan QR Absensi</h2>
                    <p class="text-gray-500 text-sm">Cukup scan kode QR untuk langsung melakukan absensi</p>
                </div>

                <div class="flex flex-col items-center justify-center">
                    <div id="reader" class="w-full max-w-sm rounded-2xl overflow-hidden border-4 border-orange-500 bg-black shadow-2xl relative">
                        {{-- Overlays will be injected here --}}
                    </div>
                    
                    <div id="scan-result" class="hidden mt-6 p-4 bg-orange-500 text-white font-bold rounded-xl w-full text-center shadow-lg">
                         Mendatangi Server...
                    </div>

                    <div id="ssl-warning" class="hidden mt-4 p-2 bg-red-100 text-red-800 text-xs rounded-lg text-center">
                         Kamera & GPS membutuhkan HTTPS.
                    </div>

                    <x-filament::button id="start-scan-btn" class="mt-8 w-full py-4 text-lg shadow-xl" color="warning" icon="heroicon-o-camera">
                        Buka Kamera / Scan
                    </x-filament::button>
                </div>
            </x-filament::section>

            <div class="hidden">
                {{ $this->form }}
            </div>
        </div>

        {{-- GPS Status --}}
        <div id="gps-status" class="fixed top-20 right-4 z-[60] flex items-center gap-x-2 px-3 py-1.5 rounded-full bg-white dark:bg-gray-800 shadow-2xl border border-gray-100 dark:border-gray-700 opacity-0 transition-all duration-500 transform translate-y-2">
            <div class="h-2.5 w-2.5 rounded-full bg-red-500" id="gps-dot"></div>
            <span class="text-[10px] md:text-xs font-black text-gray-700 dark:text-gray-300" id="gps-text">Checking GPS...</span>
            <button onclick="updateGPS(true)" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors ml-1" title="Refresh Lokasi">
                 <x-filament::icon icon="heroicon-o-arrow-path" class="w-3.5 h-3.5 text-gray-400" />
            </button>
        </div>

        {{-- Universal Processing Overlay --}}
        <div id="global-processing" class="fixed inset-0 z-[100] bg-black/80 hidden flex-col items-center justify-center backdrop-blur-xl">
            <div class="bg-white dark:bg-gray-900 p-12 rounded-[2.5rem] shadow-2xl flex flex-col items-center max-w-xs w-[85%] text-center border border-white/10">
                <div class="relative w-24 h-24 mb-10">
                    <div class="absolute inset-0 border-[5px] border-orange-500/10 rounded-full"></div>
                    <div class="absolute inset-0 border-[5px] border-orange-500 border-t-transparent rounded-full animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-arrow-up-on-square" class="w-10 h-10 text-orange-500 animate-pulse" />
                    </div>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3">Sync Absensi...</h3>
                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed font-medium">Jangan pindah halaman atau mematikan layar saat sinkronisasi berlangsung.</p>
            </div>
        </div>

        <style>
            #reader { border: none !important; min-height: 300px; }
            #reader video { object-fit: cover !important; border-radius: 1.5rem; width: 100% !important; height: 100% !important; }
            #reader__dashboard { display: none !important; }
            
            /* Premium Scan Line */
            .scan-line {
                position: absolute;
                top: 15%; left: 5%; width: 90%; height: 3px;
                background: linear-gradient(to right, transparent, #f97316, #fbbf24, #f97316, transparent);
                box-shadow: 0 0 25px rgba(249, 115, 22, 0.9);
                border-radius: 50%;
                animation: line-scan 2.2s infinite ease-in-out;
                z-index: 30;
            }

            @keyframes line-scan {
                0%, 100% { top: 15%; opacity: 0.2; }
                50% { top: 85%; opacity: 1; }
            }

            .scan-frame {
                position: absolute; top: 0; left: 0; right: 0; bottom: 0;
                z-index: 20; pointer-events: none;
                display: flex; align-items: center; justify-content: center;
            }

            .scan-focus-area {
                width: 250px; height: 250px;
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 0 0 4000px rgba(0,0,0,0.7);
                border-radius: 40px;
                position: relative;
            }

            .c-marker {
                position: absolute; width: 45px; height: 45px;
                border: 6px solid #f97316;
                filter: drop-shadow(0 0 10px rgba(249, 115, 22, 0.6));
            }
            .m-tl { top: -3px; left: -3px; border-right: 0; border-bottom: 0; border-radius: 25px 0 0 0; }
            .m-tr { top: -3px; right: -3px; border-left: 0; border-bottom: 0; border-radius: 0 25px 0 0; }
            .m-bl { bottom: -3px; left: -3px; border-right: 0; border-top: 0; border-radius: 0 0 0 25px; }
            .m-br { bottom: -3px; right: -3px; border-left: 0; border-top: 0; border-radius: 0 0 25px 0; }
        </style>

        <script>
            // --- PRO-GRADE STICKY GPS ---
            let gpsStatus = 'idle';
            let watchId = null;

            function updateGPS(force = false) {
                if (!navigator.geolocation) {
                    renderGPSUI('error', 'Browser Not Supported');
                    return;
                }

                if (force) {
                    gpsStatus = 'locking';
                    renderGPSUI('loading', 'Mencari Sinyal...');
                }

                const options = {
                    enableHighAccuracy: true,
                    timeout: 20000,
                    maximumAge: 5000 
                };

                const onLocationSuccess = (pos) => {
                    window.userLat = pos.coords.latitude;
                    window.userLong = pos.coords.longitude;
                    gpsStatus = 'locked';
                    
                    renderGPSUI('success', `Lokasi Terkunci (${window.userLat.toFixed(4)})`);
                    
                    // Keep Livewire properties updated in background
                    @this.set('user_lat', window.userLat, true);
                    @this.set('user_long', window.userLong, true);
                };

                const onLocationError = (err) => {
                    console.warn("GPS Error:", err.message);
                    if (gpsStatus !== 'locked') {
                        renderGPSUI('error', 'Gagal Mengunci Lokasi');
                        // Fallback to less accurate if needed
                    }
                };

                if (watchId) navigator.geolocation.clearWatch(watchId);
                watchId = navigator.geolocation.watchPosition(onLocationSuccess, onLocationError, options);
            }

            function renderGPSUI(type, text) {
                const el = document.getElementById('gps-status');
                const dot = document.getElementById('gps-dot');
                const txt = document.getElementById('gps-text');
                
                if (!el) return;
                el.classList.remove('opacity-0', 'translate-y-2');
                el.classList.add('opacity-100', 'translate-y-0');

                if (type === 'success') {
                    dot.className = "h-2.5 w-2.5 rounded-full bg-green-500 shadow-[0_0_8px_#22c55e]";
                    txt.className = "text-[10px] md:text-xs font-black text-green-600 dark:text-green-400";
                } else if (type === 'error') {
                    dot.className = "h-2.5 w-2.5 rounded-full bg-red-500 animate-pulse";
                    txt.className = "text-[10px] md:text-xs font-bold text-red-500";
                } else {
                    dot.className = "h-2.5 w-2.5 rounded-full bg-orange-400 animate-bounce";
                    txt.className = "text-[10px] md:text-xs font-medium text-orange-500";
                }
                txt.innerText = text;
            }

            document.addEventListener('DOMContentLoaded', () => updateGPS());

            // --- SCANNER SYSTEM ---
            let scanner = null;
            let isActive = false;
            let lastScan = 0;

            async function processScan(code) {
                const now = Date.now();
                if (isActive || (now - lastScan < 7000)) return;

                isActive = true;
                lastScan = now;

                if (navigator.vibrate) navigator.vibrate([150, 50, 150]);

                const overlay = document.getElementById('global-processing');
                const results = document.getElementById('scan-result');
                
                overlay.classList.remove('hidden');
                if (results) {
                    results.innerText = "QR Ditemukan! Menghubungkan ke Pusat...";
                    results.classList.remove('hidden', 'bg-red-500', 'bg-green-600');
                    results.classList.add('bg-orange-500');
                }

                if (scanner) await scanner.pause();

                try {
                    if (!window.userLat || !window.userLong) {
                        throw new Error("Izin Lokasi/GPS Diperlukan!");
                    }

                    // Push properties to Livewire
                    await @this.set('user_lat', window.userLat);
                    await @this.set('user_long', window.userLong);
                    await @this.set('qr_payload', code);
                    
                    // Confirm with server
                    await @this.call('saveAttendance');
                    
                } catch (error) {
                    console.error("Submission Error:", error);
                    document.getElementById('global-processing').classList.add('hidden');
                    isActive = false;
                    
                    if (results) {
                        results.innerText = "Gagal: " + error.message;
                        results.classList.replace('bg-orange-500', 'bg-red-500');
                    }
                    
                    if (confirm("Koneksi terputus. Coba lagi?")) {
                        if (scanner) scanner.resume();
                    }
                }
            }

            // --- SERVER RESPONSES ---
            window.addEventListener('attendance-success', () => {
                document.getElementById('global-processing').classList.add('hidden');
                const results = document.getElementById('scan-result');
                if (results) {
                    results.innerText = "ABSENSI BERHASIL DISIMPAN!";
                    results.classList.replace('bg-orange-500', 'bg-green-600');
                    setTimeout(() => results.classList.add('hidden'), 7000);
                }
                
                setTimeout(() => { 
                    isActive = false; 
                    if (scanner) scanner.resume(); 
                }, 7000);
            });

            window.addEventListener('attendance-failure', (e) => {
                document.getElementById('global-processing').classList.add('hidden');
                const results = document.getElementById('scan-result');
                if (results) {
                    results.innerText = "GAGAL: " + (e.detail.error || 'Server Side Error');
                    results.classList.replace('bg-orange-500', 'bg-red-500');
                }
                setTimeout(() => { 
                    isActive = false; 
                    if (scanner) scanner.resume(); 
                }, 4000);
            });

            document.getElementById('start-scan-btn').addEventListener('click', () => {
                if (scanner) {
                    scanner.clear().then(initScanner).catch(initScanner);
                } else {
                    initScanner();
                }
            });

            function initScanner() {
                const reader = document.getElementById('reader');
                if (!reader.querySelector('.scan-frame')) {
                    reader.insertAdjacentHTML('beforeend', `
                        <div class="scan-frame">
                            <div class="scan-line"></div>
                            <div class="scan-focus-area">
                                <div class="c-marker m-tl"></div>
                                <div class="c-marker m-tr"></div>
                                <div class="c-marker m-bl"></div>
                                <div class="c-marker m-br"></div>
                            </div>
                        </div>
                    `);
                }

                scanner = new Html5QrcodeScanner("reader", { 
                    fps: 30, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    videoConstraints: {
                        facingMode: "environment",
                        focusMode: "continuous",
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                }, false);
                scanner.render(processScan, () => {});
            }
        </script>
    </x-filament-panels::page>
</div>
