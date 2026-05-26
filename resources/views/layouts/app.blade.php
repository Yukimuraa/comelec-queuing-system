<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            background: radial-gradient(circle at 50% 50%, #111827 0%, #030712 100%);
            color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Glassmorphism utility */
        .glass-panel {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 30px 0 rgba(0, 0, 0, 0.3);
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
            background: rgba(0, 0, 0, 0.2);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
    @yield('styles')
</head>
<body class="antialiased">
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
                <span class="font-bold text-lg tracking-tight bg-gradient-to-r from-white via-gray-200 to-gray-400 bg-clip-text text-transparent">COMELEC QMS</span>
                <span class="text-xs text-gray-500 font-medium tracking-wider uppercase">Queue Management System</span>
            </div>
        </a>

        <!-- Portal quick links -->
        <nav class="flex items-center gap-2 md:gap-4">
            <a href="{{ url('/') }}" class="px-4 py-2 text-sm font-semibold rounded-lg hover:bg-gray-800 transition-colors text-gray-300">
                Portal Home
            </a>
            <a href="{{ route('client.scan') }}" class="px-4 py-2 text-sm font-semibold rounded-lg hover:bg-gray-800 transition-colors text-gray-300 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Scan Station
            </a>
            <a href="{{ route('display.tv') }}" class="px-4 py-2 text-sm font-semibold rounded-lg hover:bg-gray-800 transition-colors text-gray-300 flex items-center gap-1.5">
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
    <footer class="w-full text-center py-6 text-xs text-gray-600 border-t border-gray-900 mt-auto">
        <p>&copy; {{ date('Y') }} Commission on Elections. All Rights Reserved. Powered by SQLite & Laravel.</p>
    </footer>

    @yield('scripts')
</body>
</html>
