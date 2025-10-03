<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
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
                    </div>
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