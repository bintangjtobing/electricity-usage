<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk &mdash; {{ config('app.name') }}</title>
    <meta name="theme-color" content="#111827">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-900 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">{{ config('app.name') }}</h1>
                <p class="text-sm text-gray-400 mt-1">Masuk untuk melanjutkan</p>
            </div>

            <form method="POST" action="{{ route('login') }}"
                  class="bg-gray-800 rounded-xl shadow-lg p-8 border border-gray-700 space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                           class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember"
                           class="h-4 w-4 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-gray-400">Ingat saya</label>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
