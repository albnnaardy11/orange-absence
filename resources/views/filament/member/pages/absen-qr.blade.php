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
                    ✅ QR Terdeteksi! Memproses...
                </div>

                <div id="ssl-warning" class="hidden mt-4 p-2 bg-red-100 text-red-800 text-xs rounded-lg text-center">
                    ⚠️ Kamera & GPS membutuhkan HTTPS.
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
    <div id="gps-status" class="fixed top-18 right-4 z-50 flex items-center gap-x-2 px-3 py-1.5 rounded-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 opacity-0 transition-all">
        <div class="h-2 w-2 rounded-full bg-red-500" id="gps-dot"></div>
        <span class="text-xs font-bold" id="gps-text">GPS...</span>
    </div>

    <script>
        function updateGPS() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    @this.set('user_lat', pos.coords.latitude);
                    @this.set('user_long', pos.coords.longitude);
                    document.getElementById('gps-status').classList.remove('opacity-0');
                    document.getElementById('gps-dot').classList.replace('bg-red-500', 'bg-green-500');
                    document.getElementById('gps-text').innerText = 'GPS: OK';
                });
            }
        }
        document.addEventListener('DOMContentLoaded', updateGPS);

        let html5QrcodeScanner = null;
        function onScanSuccess(decodedText) {
            // Visual feedback
            const resDiv = document.getElementById('scan-result');
            resDiv.classList.remove('hidden');
            
            // Auto submit to Filament
            @this.call('submit', decodedText).then(() => {
                setTimeout(() => { resDiv.classList.add('hidden'); }, 3000);
            });

            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(error => console.error("Failed to clear scanner", error));
            }
        }

        document.getElementById('start-scan-btn').addEventListener('click', () => {
            document.getElementById('scan-result').classList.add('hidden');
            
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().then(() => {
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 15, qrbox: 250 });
                    html5QrcodeScanner.render(onScanSuccess);
                }).catch(err => {
                    html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 15, qrbox: 250 });
                    html5QrcodeScanner.render(onScanSuccess);
                });
            } else {
                html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 15, qrbox: 250 });
                html5QrcodeScanner.render(onScanSuccess);
            }
        });
    </script>
</x-filament-panels::page>
