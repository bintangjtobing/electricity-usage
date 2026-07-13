<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- PWA: bisa dipasang ke home screen dan dibuka tanpa address bar -->
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Listrik">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="icon" href="{{ asset('icons/icon-192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-900 font-sans antialiased">
    <nav class="bg-gray-800 shadow-lg border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-white">Electricity Usage Monitor</h1>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('dashboard') }}" 
                           class="{{ request()->routeIs('dashboard') ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Dashboard
                        </a>
                        <a href="{{ route('purchase') }}" 
                           class="{{ request()->routeIs('purchase') ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Pembelian
                        </a>
                        <a href="{{ route('check') }}" 
                           class="{{ request()->routeIs('check') ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Cek Sisa
                        </a>
                        <a href="{{ route('history') }}"
                           class="{{ request()->routeIs('history') ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Riwayat
                        </a>
                        <a href="{{ route('settings') }}"
                           class="{{ request()->routeIs('settings') ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-gray-400 hover:border-gray-600 hover:text-gray-300' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            Pengaturan
                        </a>
                    </div>
                </div>
                <div class="hidden sm:flex sm:items-center">
                    <span class="text-sm text-gray-400 mr-4">{{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-sm text-gray-400 hover:text-white transition-colors duration-200">
                            Keluar
                        </button>
                    </form>
                </div>
                <div class="sm:hidden flex items-center">
                    <button onclick="toggleMobileMenu()" 
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-300 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <div id="mobile-menu" class="sm:hidden hidden border-t border-gray-700">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="text-gray-400 hover:bg-gray-700 hover:border-gray-600 hover:text-gray-300 block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-gray-700 border-indigo-500 text-indigo-400' : 'border-transparent' }}">
                    Dashboard
                </a>
                <a href="{{ route('purchase') }}"
                   class="text-gray-400 hover:bg-gray-700 hover:border-gray-600 hover:text-gray-300 block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('purchase') ? 'bg-gray-700 border-indigo-500 text-indigo-400' : 'border-transparent' }}">
                    Pembelian
                </a>
                <a href="{{ route('check') }}"
                   class="text-gray-400 hover:bg-gray-700 hover:border-gray-600 hover:text-gray-300 block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('check') ? 'bg-gray-700 border-indigo-500 text-indigo-400' : 'border-transparent' }}">
                    Cek Sisa
                </a>
                <a href="{{ route('history') }}"
                   class="text-gray-400 hover:bg-gray-700 hover:border-gray-600 hover:text-gray-300 block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('history') ? 'bg-gray-700 border-indigo-500 text-indigo-400' : 'border-transparent' }}">
                    Riwayat
                </a>
                <a href="{{ route('settings') }}"
                   class="text-gray-400 hover:bg-gray-700 hover:border-gray-600 hover:text-gray-300 block pl-3 pr-4 py-2 border-l-4 text-base font-medium {{ request()->routeIs('settings') ? 'bg-gray-700 border-indigo-500 text-indigo-400' : 'border-transparent' }}">
                    Pengaturan
                </a>
                <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-700 mt-2 pt-2">
                    @csrf
                    <button type="submit"
                            class="w-full text-left text-gray-400 hover:bg-gray-700 hover:text-gray-300 block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="py-6">
        {{ $slot }}
    </main>

    @livewireScripts
    
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>