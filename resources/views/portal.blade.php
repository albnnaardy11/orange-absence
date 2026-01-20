<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orange Absence | Gateway Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --orange-primary: #fb923c;
            --orange-secondary: #f97316;
            --dark-bg: #0a0a0b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--dark-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(251, 146, 60, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.05) 0px, transparent 50%);
            min-height: 100vh;
            color: #e5e7eb;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .glass {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 32px;
            transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .glass:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(251, 146, 60, 0.2);
            transform: translateY(-8px) scale(1.01);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .orange-gradient-text {
            background: linear-gradient(to right, #fb923c, #f97316, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size: 200% auto;
            animation: textShine 4s ease-in-out infinite alternate;
        }

        @keyframes textShine {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        .login-card {
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.03), transparent);
            transition: 0.5s;
        }

        .login-card:hover::before {
            left: 100%;
        }

        .btn-portal-action {
            background: linear-gradient(135deg, #fb923c, #f97316);
            padding: 12px;
            border-radius: 16px;
            text-align: center;
            font-weight: 600;
            margin-top: auto;
            transition: all 0.3s ease;
            color: white;
        }

        .glass:hover .btn-portal-action {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.3);
        }

        .hero-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(249, 115, 22, 0.1) 0%, transparent 70%);
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body class="p-6">
    <div class="hero-glow"></div>

    <div class="max-w-6xl mx-auto w-full">
        <!-- Header -->
        <header class="text-center mb-20">
            <h1 class="text-7xl font-extrabold mb-6 tracking-tight">
                <span class="orange-gradient-text">ABSEN ORANGE</span>
            </h1>
            <p class="text-gray-400 text-xl font-light max-w-2xl mx-auto">
                Portal Akses Sistem Absensi & Kas Orange. 
                Pilih peran Anda untuk masuk ke sistem.
            </p>
        </header>

        <!-- Main Content -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            
            <!-- Administrator Section -->
            <a href="/admin/login" class="glass p-10 login-card">
                <div class="mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 2.944V21m0-18.056L3 9m9-6.056l9 6.056"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold">Administrator</h2>
                    <p class="text-gray-500 mt-2 text-sm leading-relaxed">Kelola seluruh sistem, data divisi, laporan, dan infrastruktur.</p>
                </div>
                <div class="btn-portal-action">Masuk sebagai Admin</div>
            </a>

            <!-- Secretary Section -->
            <a href="/secretary" class="glass p-10 login-card">
                <div class="mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 01-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold">Secretary</h2>
                    <p class="text-gray-500 mt-2 text-sm leading-relaxed">Kelola operasional divisi, buku kas, dan verifikasi absensi harian.</p>
                </div>
                <div class="btn-portal-action">Masuk sebagai Sekretaris</div>
            </a>

            <!-- Member Section -->
            <a href="/member/login" class="glass p-10 login-card">
                <div class="mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-orange-500/10 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold">Member</h2>
                    <p class="text-gray-500 mt-2 text-sm leading-relaxed">Presensi harian, pantau statistik kehadiran harian, dan jadwal kegiatan.</p>
                </div>
                <div class="btn-portal-action">Masuk sebagai Member</div>
            </a>

        </div>

        <!-- Footer -->
        <footer class="mt-24 text-center">
            <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full bg-white/5 border border-white/5 text-xs text-gray-500">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                <span>Status: Online</span>
            </div>
            <p class="text-gray-600 text-sm mt-6 font-light opacity-50">&copy; {{ date('Y') }} Orange Absence. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
