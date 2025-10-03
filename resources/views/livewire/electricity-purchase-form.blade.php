<div class="min-h-screen bg-gray-900 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-800 rounded-xl shadow-lg p-8 border border-gray-700">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-white">Form Pembelian Listrik</h2>
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
            
            @if (session()->has('message'))
                <div class="mb-6 bg-green-900 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                    <div class="flex">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">{{ session('message') }}</span>
                    </div>
                </div>
            @endif

            <form wire:submit="submit" class="space-y-6">
                <!-- Info Meter -->
                <div class="bg-gray-700 rounded-lg p-6 border-l-4 border-blue-500">
                    <h3 class="text-lg font-semibold text-white mb-4">Informasi Meter</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Alamat</label>
                            <p class="text-sm text-gray-400">Jl. Gunung Lumut No.62, Padangsambian Klod, Kec. Denpasar Bar., Kota 80117</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">No Meter</label>
                            <p class="text-lg font-semibold text-white">{{ $meter_number }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Pemilik</label>
                            <p class="text-sm text-white">{{ $owner_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tarif / Daya</label>
                            <p class="text-sm text-white">{{ $tariff_type }}</p>
                        </div>
                    </div>
                </div>

                <!-- Input Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="purchase_price_formatted" class="block text-sm font-medium text-gray-300 mb-2">
                            Purchase Price <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" 
                                   id="purchase_price_formatted"
                                   wire:model.live="purchase_price_formatted" 
                                   class="block w-full pl-12 pr-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('purchase_price') border-red-500 @enderror"
                                   placeholder="250.000">
                        </div>
                        @error('purchase_price') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Format otomatis dengan titik sebagai pemisah ribuan</p>
                    </div>

                    <div>
                        <label for="kwh_bought" class="block text-sm font-medium text-gray-300 mb-2">
                            KwH Vol.
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="kwh_bought"
                                   wire:model.live="kwh_bought" 
                                   step="0.01"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kwh_bought') border-red-500 @enderror"
                                   placeholder="157.40">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 sm:text-sm">kWh</span>
                            </div>
                        </div>
                        @error('kwh_bought') 
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Akan otomatis terisi saat input harga</p>
                    </div>
                </div>

                <!-- Price per Unit Display -->
                <div class="bg-gray-700 rounded-lg p-4 border border-blue-500">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-blue-400">Harga per Unit (kWh)</span>
                        <span class="text-lg font-bold text-blue-400">Rp {{ number_format($price_per_unit, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Calculation Display -->
                @if($purchase_price && $kwh_bought)
                <div class="bg-gray-700 rounded-lg p-4 border border-green-500">
                    <h4 class="text-sm font-medium text-green-400 mb-2">Ringkasan Pembelian</h4>
                    <div class="space-y-1 text-sm text-green-300">
                        <div class="flex justify-between">
                            <span>Harga Pembelian:</span>
                            <span class="font-semibold">Rp {{ number_format($purchase_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Jumlah kWh:</span>
                            <span class="font-semibold">{{ number_format($kwh_bought, 2) }} kWh</span>
                        </div>
                        <div class="flex justify-between border-t border-green-500 pt-1">
                            <span>Harga per kWh:</span>
                            <span class="font-semibold">Rp {{ number_format($price_per_unit, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Pembelian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>