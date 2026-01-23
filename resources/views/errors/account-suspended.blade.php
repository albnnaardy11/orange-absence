<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended - Orange Absence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-[#f8fafc] flex items-center justify-center min-h-screen p-4">
    <div class="glass p-10 rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-white max-w-md w-full text-center">
        <div class="mb-6 inline-flex p-4 rounded-full bg-red-50 text-red-500 animate-pulse">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl font-extrabold text-[#1e293b] mb-4">Akses Terkunci</h1>
        
        <div class="space-y-4 text-[#64748b] text-lg leading-relaxed">
            <p>
                Akun Anda telah ditangguhkan sementara karena akumulasi poin pelanggaran mencapai batas maksimal (<span class="font-bold text-red-500">30 Poin</span>).
            </p>
            <div class="p-4 bg-orange-50 rounded-2xl text-orange-700 text-sm font-medium">
                Poin Anda saat ini: <span class="text-lg">{{ auth()->user()->points ?? '30+' }}</span>
            </div>
            <p class="text-sm italic">
                Silakan hubungi Administrator atau Sekretaris untuk proses reset poin dan aktivasi ulang akun.
            </p>
        </div>

        <div class="mt-10 flex flex-col gap-3">
            <a href="{{ url('/logout-suspended') }}" class="w-full py-4 bg-[#1e293b] text-white rounded-2xl font-bold hover:bg-black transition-all shadow-lg text-center">
                Kembali ke Portal & Keluar
            </a>
        </div>
    </div>
</body>
</html>
