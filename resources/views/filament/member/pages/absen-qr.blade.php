<div id="absen-qr-layout-wrapper">
<x-filament-panels::page>
<div id="absen-qr-inner-wrapper">
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<div class="max-w-full flex flex-col items-center justify-center space-y-8 py-10">
<x-filament::section class="w-full max-w-lg">
<div class="text-center mb-6">
<h2 class="text-2xl font-bold text-orange-600 dark:text-orange-500">Scan QR Absensi</h2>
<p class="text-gray-500 text-sm">Cukup scan kode QR untuk langsung melakukan absensi</p>
</div>
<div class="flex flex-col items-center justify-center">
<div id="reader" wire:ignore class="w-full max-w-sm rounded-2xl overflow-hidden border-4 border-orange-500 bg-black shadow-2xl relative"></div>
<div id="scan-result" class="hidden mt-6 p-4 bg-orange-500 text-white font-bold rounded-xl w-full text-center shadow-lg">Mendatangi Server...</div>
<div id="ssl-warning" class="hidden mt-4 p-2 bg-red-100 text-red-800 text-xs rounded-lg text-center">Kamera & GPS membutuhkan HTTPS.</div>
<x-filament::button id="start-scan-btn" class="mt-8 w-full py-4 text-lg shadow-xl" color="warning" icon="heroicon-o-camera" onclick="startScan()">Buka Kamera / Scan</x-filament::button>
</div>
</x-filament::section>
<div class="hidden">{{ $this->form }}</div>
</div>
<div id="gps-status" class="fixed top-20 right-4 z-[60] flex items-center gap-x-2 px-3 py-1.5 rounded-full bg-white dark:bg-gray-800 shadow-2xl border border-gray-100 dark:border-gray-700 opacity-0 transition-all duration-500 transform translate-y-2">
<div class="h-2.5 w-2.5 rounded-full bg-red-500" id="gps-dot"></div>
<span class="text-[10px] md:text-xs font-black text-gray-700 dark:text-gray-300" id="gps-text">Checking GPS...</span>
<button onclick="updateGPS(true)" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors ml-1" title="Refresh Lokasi">
<x-filament::icon icon="heroicon-o-arrow-path" class="w-3.5 h-3.5 text-gray-400" />
</button>
</div>
<div id="global-processing" class="fixed inset-0 z-[100] bg-black/80 hidden flex-col items-center justify-center backdrop-blur-xl">
<div class="bg-white dark:bg-gray-900 p-12 rounded-[2.5rem] shadow-2xl flex flex-col items-center max-w-xs w-[85%] text-center border border-white/10">
<div class="relative w-24 h-24 mb-10">
<div class="absolute inset-0 border-[5px] border-orange-500/10 rounded-full"></div>
<div class="absolute inset-0 border-[5px] border-orange-500 border-t-transparent rounded-full animate-spin"></div>
<div class="absolute inset-0 flex items-center justify-center"><x-filament::icon icon="heroicon-o-arrow-up-on-square" class="w-10 h-10 text-orange-500 animate-pulse" /></div>
</div>
<h3 class="text-2xl font-black text-gray-900 dark:text-white mb-3">Sync Absensi...</h3>
<p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed font-medium">Jangan pindah halaman atau mematikan layar saat sinkronisasi berlangsung.</p>
</div>
</div>
<style>
    /* Force the reader to be a clean container for the video element */
    #reader {
        width: 100%;
        height: 100%;
        background: #000;
        position: relative;
        overflow: hidden;
    }
    #reader video {
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
        border-radius: 1rem;
    }
    /* Hide the library's default UI elements if any leak through */
    #reader__dashboard_section_csr, #reader__dashboard_section_swaplink {
        display: none !important;
    }
    
    .scan-line {
        position: absolute;
        width: 100%;
        height: 3px;
        background: #f97316;
        box-shadow: 0 0 10px #f97316;
        animation: scanDown 2s infinite ease-in-out;
        z-index: 50;
        opacity: 0.8;
    }
    @keyframes scanDown {
        0%, 100% { top: 0%; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    .scan-overlay {
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        width: 70%;
        height: 60%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.5);
        z-index: 40;
    }
</style>

<script>
    let html5QrCode = null;
    let isScanning = false;
    let gpsLocked = false;

    // --- GPS Logic ---
    function initGPS() {
        if (!navigator.geolocation) {
           updateGPSUI('error', 'Browser tidak support GPS');
           return;
        }

        updateGPSUI('loading', 'Mencari Lokasi...');
        
        navigator.geolocation.watchPosition(
            (pos) => {
                window.userLat = pos.coords.latitude;
                window.userLong = pos.coords.longitude;
                gpsLocked = true;
                updateGPSUI('success', `Akurasi: ${Math.round(pos.coords.accuracy)}m`);
            },
            (err) => {
                console.warn('GPS Error', err);
                gpsLocked = false;
                updateGPSUI('error', 'GPS Mati / Ditolak');
            },
            {
                enableHighAccuracy: true,
                maximumAge: 5000,
                timeout: 10000
            }
        );
    }

    function updateGPSUI(status, text) {
        const dot = document.getElementById('gps-dot');
        const label = document.getElementById('gps-text');
        const container = document.getElementById('gps-status');
        
        // Show container
        container.classList.remove('opacity-0', 'translate-y-2');
        container.classList.add('opacity-100', 'translate-y-0');

        if (status === 'success') {
            dot.className = "h-3 w-3 rounded-full bg-green-500 shadow-[0_0_10px_#22c55e]";
            label.className = "text-xs font-bold text-green-600 dark:text-green-400";
        } else if (status === 'loading') {
            dot.className = "h-3 w-3 rounded-full bg-yellow-400 animate-pulse";
            label.className = "text-xs font-medium text-yellow-600 dark:text-yellow-400";
        } else {
            dot.className = "h-3 w-3 rounded-full bg-red-500";
            label.className = "text-xs font-bold text-red-600 dark:text-red-400";
        }
        label.innerText = text;
    }

    // --- Camera Logic ---
    async function startScan() {
        const statusMsg = document.getElementById('scan-result');
        
        // 1. Check HTTPS
        if (location.protocol !== 'https:' && location.hostname !== 'localhost' && !location.hostname.startsWith('127.0.0.')) {
            alert("Error Kritis: Fitur Kamera HANYA jalan di HTTPS (Gembok Hijau). Hosting Anda mungkin masih HTTP.");
            return;
        }

        // 2. Check Instance
        if (isScanning) {
            // Stop logic if button clicked while scanning
             if (html5QrCode) {
                await html5QrCode.stop();
                isScanning = false;
                document.getElementById('reader').innerHTML = ''; // Clean cleanup
             }
             return;
        }

        // 3. UI Prep
        statusMsg.classList.remove('hidden', 'bg-red-500', 'bg-green-600');
        statusMsg.classList.add('bg-orange-500');
        statusMsg.innerText = "Membuka Kamera...";
        statusMsg.classList.remove('hidden');

        try {
            // 4. Initialize Core Library
            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            // 5. Start Camera
            const config = { 
                fps: 30, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };
            
            await html5QrCode.start(
                { facingMode: "environment" }, // Prefer Back Camera
                config,
                onScanSuccess,
                onScanFailure
            );

            // 6. Success State
            isScanning = true;
            statusMsg.classList.add('hidden'); // Hide "Opening..." message when video starts
            
            // Add Overlay
            const reader = document.getElementById('reader');
            if (!reader.querySelector('.scan-overlay')) {
                reader.insertAdjacentHTML('beforeend', '<div class="scan-overlay"></div><div class="scan-line"></div>');
            }

        } catch (err) {
            console.error("Camera Start Error:", err);
            isScanning = false;
            
            let errorText = "Gagal membuka kamera.";
            if (err?.name === 'NotAllowedError') {
                 errorText = "Izin Kamera Ditolak! Harap Check Settings Browser Anda → Site Settings → Camera → Allow.";
            } else if (err?.name === 'NotFoundError') {
                 errorText = "Kamera tidak ditemukan di perangkat ini.";
            } else if (err?.name === 'NotReadableError') {
                 errorText = "Kamera sedang digunakan aplikasi lain atau error hardware.";
            } else if (location.protocol !== 'https:') {
                 errorText = "Wajib HTTPS agar kamera berjalan.";
            }

            statusMsg.innerText = errorText;
            statusMsg.classList.replace('bg-orange-500', 'bg-red-500');
            statusMsg.classList.remove('hidden');
            
            alert(errorText); // Force alert for mobile users to see immediately
        }
    }

    let isProcessing = false;
    async function onScanSuccess(decodedText, decodedResult) {
        if (isProcessing) return;
        
        isProcessing = true;
        if (navigator.vibrate) navigator.vibrate(200);

        // UI Feedback
        const overlay = document.getElementById('global-processing');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');

        try {
            // Stop camera immediately
            if (html5QrCode) {
                await html5QrCode.pause();
            }

            if (!gpsLocked && !window.userLat) {
               throw new Error("GPS Belum Terkunci! Tunggu indikator GPS hijau.");
            }

            // DYNAMIC COMPONENT LOOKUP (Fix for 'call' undefined error)
            // We find the component instance at the exact moment of execution
            const componentId = '{{ $this->getId() }}';
            const component = Livewire.find(componentId);
            
            if (!component) {
                // Determine if we are in a stale state or just lost connection
                console.error("Livewire component not found: " + componentId);
                throw new Error("Koneksi terputus. Mohon refresh halaman.");
            }

            // Send to Server
            await component.call('saveAttendance', decodedText, window.userLat, window.userLong);

        } catch(e) {
            console.error("Scan Process Error", e);
            const resDiv = document.getElementById('scan-result');
            resDiv.innerText = " " + (e.message || "Gagal Memproses");
            resDiv.classList.remove('hidden');
            resDiv.classList.add('bg-red-500');
            
            // Resume after error
            setTimeout(() => {
                isProcessing = false;
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                if (html5QrCode) html5QrCode.resume();
            }, 3000);
        }
    }

    function onScanFailure(error) {
        // console.warn(`Code scan error = ${error}`);
        // excessive logging causes lag, better keep silent for "no QR found" frames
    }

    // --- Listeners ---
    document.addEventListener('DOMContentLoaded', initGPS);

    // Livewire Events
    window.addEventListener('attendance-success', () => {
         const overlay = document.getElementById('global-processing');
         overlay.classList.add('hidden');
         overlay.classList.remove('flex');
         
         const resDiv = document.getElementById('scan-result');
         resDiv.innerText = "Berhasil Absen!";
         resDiv.classList.replace('bg-orange-500', 'bg-green-600');
         resDiv.classList.replace('bg-red-500', 'bg-green-600');
         resDiv.classList.remove('hidden');

         // Cleanup
         isProcessing = false;
         isScanning = false;
         if (html5QrCode) {
             html5QrCode.stop().then(() => {
                 html5QrCode.clear();
             });
         }
    });

    window.addEventListener('attendance-failure', (e) => {
         const overlay = document.getElementById('global-processing');
         overlay.classList.add('hidden');
         overlay.classList.remove('flex');

         const resDiv = document.getElementById('scan-result');
         resDiv.innerText = " " + (e.detail.error || 'Gagal');
         resDiv.classList.add('bg-red-500');
         resDiv.classList.remove('hidden');
         
         isProcessing = false;
         setTimeout(() => {
             if (html5QrCode) html5QrCode.resume();
         }, 2000);
    });

</script>
</div>

</x-filament-panels::page>
</div>
