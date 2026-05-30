<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="COMELEC QMS">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('icons/icon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon.svg') }}">
    <title>QMS - Queue Management System</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Play CDN Fallback for styling without compiling -->
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            /* Light mode background (requested: light grey) */
            background: #f3f4f6;
            color: #111827;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        html.dark body {
            background: #0b1220;
            color: #ffffff;
        }

        html.dark .glass-panel {
            background: rgba(17, 24, 39, 0.75);
            border-color: rgba(255, 255, 255, 0.06);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.35);
        }

        html.dark footer {
            border-color: rgba(255, 255, 255, 0.08);
            color: rgba(229, 231, 235, 0.7);
        }

        /* Force common Tailwind gray text colors to be readable in dark mode */
        html.dark .text-gray-900,
        html.dark .text-gray-800,
        html.dark .text-gray-700,
        html.dark .text-gray-650,
        html.dark .text-gray-600 {
            color: #ffffff !important;
        }

        html.dark .text-gray-500,
        html.dark .text-gray-400 {
            color: rgba(229, 231, 235, 0.85) !important;
        }

        html.dark .border-gray-200,
        html.dark .border-gray-300 {
            border-color: rgba(255, 255, 255, 0.12) !important;
        }

        html.dark .bg-white,
        html.dark .bg-gray-50,
        html.dark .bg-white\/40,
        html.dark .bg-white\/20 {
            background-color: rgba(17, 24, 39, 0.55) !important;
        }

        html.dark input,
        html.dark select,
        html.dark textarea {
            color: #ffffff;
            background-color: rgba(17, 24, 39, 0.65);
        }

        /* Glassmorphism utility */
        .glass-panel {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.05);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 0, 0, 0.04);
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.04);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px 0 rgba(99, 102, 241, 0.15);
        }

        /* Ambient glows */
        .glow-blue {
            box-shadow: 0 0 50px -10px rgba(59, 130, 246, 0.15);
        }
        .glow-indigo {
            box-shadow: 0 0 50px -10px rgba(99, 102, 241, 0.2);
        }
        .glow-emerald {
            box-shadow: 0 0 50px -10px rgba(16, 185, 129, 0.15);
        }

        /* Smooth page transitions */
        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* PWA standalone gate — hide app in regular browser (production) */
        html.pwa-blocked body > header,
        html.pwa-blocked body > main,
        html.pwa-blocked body > footer {
            display: none !important;
        }
        html.pwa-blocked #pwaGate {
            display: flex !important;
        }
    </style>
    <script src="{{ asset('js/qms-voice.js') }}"></script>
    <script src="{{ asset('js/qms-pwa.js') }}" defer></script>
    @yield('styles')
</head>
<body class="antialiased">

    {{-- PWA install gate (shown when not in standalone/app mode) --}}
    <div id="pwaGate" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-blue-700 to-indigo-900 p-6">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-8 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-xl font-extrabold text-gray-900 mb-2">Open COMELEC QMS App</h1>
            <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                This system runs in <strong>App mode only</strong>. Close this browser tab and double-click
                <strong>start_comelec_qms.bat</strong> on your desktop or project folder.
            </p>
            <button id="pwaInstallBtn" type="button" class="hidden w-full py-3 mb-3 bg-blue-600 text-white font-bold rounded-xl border border-blue-600 shadow-lg">
                Install App
            </button>
            <div class="text-left text-xs text-gray-500 space-y-2 bg-gray-50 rounded-xl p-4 border border-gray-200">
                <p><strong>Do not use</strong> a normal browser bookmark or address bar.</p>
                <p><strong>Use:</strong> <code>start_comelec_qms.bat</code> — it opens QMS in app mode automatically.</p>
            </div>
        </div>
    </div>
    <!-- Navbar / Header -->
    <header class="w-full glass-panel py-4 px-6 md:px-12 flex items-center justify-between z-10">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
            <!-- App Logo -->
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="flex flex-col">
                <span class="font-bold text-lg tracking-tight text-gray-900">COMELEC QMS</span>
                <span class="text-xs text-gray-500 font-medium tracking-wider uppercase">Queue Management System</span>
            </div>
        </a>

        <!-- Live Clock -->
        <div class="hidden md:flex items-center gap-3 bg-white/40 border border-gray-200 px-4 py-1.5 rounded-2xl">
            <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center border border-indigo-500/20 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="flex flex-col text-left">
                <span id="liveTime" class="text-sm font-extrabold text-gray-900 font-mono leading-none tracking-wide"></span>
                <span id="liveDate" class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mt-1.5 leading-none"></span>
            </div>
        </div>

        <!-- Portal quick links + theme toggle -->
        <nav class="flex items-center gap-2 md:gap-4">
            <button id="themeToggle"
                type="button"
                class="px-3 py-2 text-sm font-semibold rounded-lg border border-gray-200 bg-white/40 hover:bg-gray-100 transition-colors text-gray-600 flex items-center gap-2"
                aria-label="Toggle light or dark mode">
                <span id="themeIcon" class="w-4 h-4 inline-flex items-center justify-center">
                    <!-- icon injected by JS -->
                </span>
                <span id="themeLabel" class="hidden md:inline">Light</span>
            </button>
            <a href="{{ url('/') }}" class="px-4 py-2 text-sm font-semibold rounded-lg hover:bg-gray-100 transition-colors text-gray-600">
                Portal Home
            </a>
            @if(request()->is('admin*'))
            <a href="{{ route('admin.report') }}" class="px-4 py-2 text-sm font-semibold rounded-lg hover:bg-gray-100 transition-colors text-gray-600 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                Daily Report
            </a>
            @endif
            <a href="{{ route('display.tv') }}" class="px-4 py-2 text-sm font-semibold rounded-lg hover:bg-gray-100 transition-colors text-gray-600 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                TV Display
            </a>
        </nav>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 md:px-8 py-8 flex flex-col z-0">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="w-full text-center py-6 text-xs text-gray-500 border-t border-gray-200 mt-auto">
        <p>&copy; {{ date('Y') }} Commission on Elections. All Rights Reserved. Powered by SQLite & Laravel.</p>
    </footer>

    @yield('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Theme toggle (persisted)
            const root = document.documentElement;
            const toggleBtn = document.getElementById('themeToggle');
            const themeLabel = document.getElementById('themeLabel');
            const themeIcon = document.getElementById('themeIcon');

            function setTheme(mode) {
                if (mode === 'dark') root.classList.add('dark');
                else root.classList.remove('dark');
                try { localStorage.setItem('qms_theme', mode); } catch (e) {}

                if (themeLabel) themeLabel.textContent = mode === 'dark' ? 'Dark' : 'Light';
                if (themeIcon) {
                    themeIcon.innerHTML = mode === 'dark'
                        ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8 8 0 1010.586 10.586z"/></svg>'
                        : '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 2.47a1 1 0 010 1.41l-.71.71a1 1 0 11-1.41-1.41l.71-.71a1 1 0 011.41 0zM18 9a1 1 0 100 2h-1a1 1 0 100-2h1zM14.22 15.12a1 1 0 00-1.41 1.41l.71.71a1 1 0 001.41-1.41l-.71-.71zM11 16a1 1 0 10-2 0v1a1 1 0 102 0v-1zM5.78 15.12l-.71.71a1 1 0 101.41 1.41l.71-.71a1 1 0 10-1.41-1.41zM3 11a1 1 0 100-2H2a1 1 0 100 2h1zm2.78-6.12a1 1 0 00-1.41 1.41l.71.71A1 1 0 105.78 6.3l-.71-.71zM10 6a4 4 0 100 8 4 4 0 000-8z" clip-rule="evenodd"/></svg>';
                }
            }

            let saved = null;
            try { saved = localStorage.getItem('qms_theme'); } catch (e) {}
            if (!saved) saved = 'light';
            setTheme(saved);

            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => {
                    const next = root.classList.contains('dark') ? 'light' : 'dark';
                    setTheme(next);
                });
            }

            // Live Clock
            function updateLiveTime() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit', 
                    hour12: true 
                });
                const dateStr = now.toLocaleDateString('en-US', { 
                    weekday: 'short', 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                
                const timeEl = document.getElementById('liveTime');
                const dateEl = document.getElementById('liveDate');
                if (timeEl) timeEl.textContent = timeStr;
                if (dateEl) dateEl.textContent = dateStr;
            }
            updateLiveTime();
            setInterval(updateLiveTime, 1000);

            // Global Device Time Formatter Helper
            window.formatDeviceTimes = function() {
                const elements = document.querySelectorAll('.device-time');
                elements.forEach(el => {
                    const timestamp = el.getAttribute('data-timestamp');
                    if (timestamp) {
                        const date = new Date(timestamp);
                        if (!isNaN(date.getTime())) {
                            const showSeconds = el.getAttribute('data-seconds') === 'true';
                            el.textContent = date.toLocaleTimeString([], { 
                                hour: '2-digit', 
                                minute: '2-digit',
                                second: showSeconds ? '2-digit' : undefined,
                                hour12: true 
                            });
                        } else {
                            el.textContent = '---';
                        }
                    }
                });
            };

            // Initial call to format times on page load
            window.formatDeviceTimes();
        });
    </script>
</body>
</html>
