<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-gray-900">Electricity Usage Check</h2>
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
            
            @if (session()->has('message'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('message') }}</span>
                    </div>
                </div>
            @endif

            <p class="text-red-500 text-sm mb-6 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                * Indicates required question
            </p>

            <form wire:submit="submit" class="space-y-6">
                <!-- Info Card -->
                <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-blue-900">Pengecekan Sisa Listrik</h3>
                    </div>
                    <p class="text-sm text-blue-800">Masukkan sisa kWh yang tertera pada meter listrik Anda untuk memantau penggunaan dan membuat proyeksi yang akurat.</p>
                </div>

                <!-- Input Field -->
                <div>
                    <label for="kwh_remaining" class="block text-lg font-semibold text-gray-900 mb-3">
                        KwH Vol Check <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               id="kwh_remaining"
                               wire:model="kwh_remaining" 
                               step="0.01"
                               class="block w-full px-4 py-4 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 @error('kwh_remaining') border-red-500 @enderror"
                               placeholder="Contoh: 62.40">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-lg font-medium">kWh</span>
                        </div>
                    </div>
                    @error('kwh_remaining') 
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p> 
                    @enderror
                    <p class="mt-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Lihat nilai sisa kWh pada layar meter listrik Anda
                    </p>
                </div>

                <!-- Instructions -->
                <div class="bg-yellow-50 rounded-lg p-6 border border-yellow-200">
                    <h4 class="text-sm font-semibold text-yellow-900 mb-2 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        Cara Melihat Sisa kWh di Meter:
                    </h4>
                    <ul class="text-sm text-yellow-800 space-y-1 ml-6">
                        <li>• Tekan tombol pada meter listrik</li>
                        <li>• Tunggu hingga muncul angka sisa kWh</li>
                        <li>• Masukkan angka tersebut (gunakan titik untuk desimal)</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Pengecekan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>