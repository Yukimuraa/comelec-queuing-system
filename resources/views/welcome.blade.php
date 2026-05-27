<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QMS Portal - Queue Management System</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Play CDN Fallback for styling without compiling -->
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 50% 50%, #ffffff 0%, #f3f4f6 100%);
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        .portal-glow {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(99, 102, 241, 0.05) 50%, transparent 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }

        .glass-portal-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-portal-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(99, 102, 241, 0.15);
        }
    </style>
</head>
<body class="p-6">
    <div class="portal-glow"></div>

    <div class="w-full max-w-5xl z-10 flex flex-col items-center">
        <!-- Logo Header -->
        <div class="flex flex-col items-center mb-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-violet-600 flex items-center justify-center shadow-2xl mb-4 animate-bounce">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight">
                Queue Management System
            </h1>
            <p class="text-gray-500 mt-2 font-medium tracking-wide uppercase text-sm">
                Commission on Elections (COMELEC) Portal
            </p>
        </div>

        <!-- System Interface Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 w-full">
            
            <!-- Card 1: Admin Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="glass-portal-card rounded-3xl p-8 flex flex-col h-80 justify-between group">
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-indigo-600 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">QMS Dashboard</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Manage tokens in real-time, view FIFO queue status, modify daily capacity limits, and print QR ticket templates.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-indigo-400 text-sm font-bold mt-4">
                    <span>Enter Dashboard</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </a>

            <!-- Card 2: Client Scanner Station -->
            <a href="{{ route('client.scan') }}" class="glass-portal-card rounded-3xl p-8 flex flex-col h-80 justify-between group">
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center text-emerald-600 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Client Scan Station</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Allows clients to scan their pre-printed QR code tokens using physical barcode scanners or an attached web camera.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-emerald-400 text-sm font-bold mt-4">
                    <span>Launch Scan Station</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </a>

            <!-- Card 3: TV Display Screen -->
            <a href="{{ route('display.tv') }}" class="glass-portal-card rounded-3xl p-8 flex flex-col h-80 justify-between group">
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-600 mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">TV Live Display</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Dedicated queue monitor for waiting rooms. Broadcasts currently serving and upcoming tokens with voice calling.
                    </p>
                </div>
                <div class="flex items-center gap-2 text-blue-400 text-sm font-bold mt-4">
                    <span>Launch TV Monitor</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </a>

        </div>
    </div>
</body>
</html>
