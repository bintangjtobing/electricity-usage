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
                            <p class="text-sm text-gray-400">{{ $address }}</p>
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

                <!-- Tanggal Pembelian -->
                <div class="md:w-1/2 md:pr-3">
                    <label for="purchase_date" class="block text-sm font-medium text-gray-300 mb-2">
                        Tanggal Pembelian <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           id="purchase_date"
                           wire:model="purchase_date"
                           value="{{ $purchase_date }}"
                           max="{{ now()->format('Y-m-d') }}"
                           style="color-scheme: dark;"
                           class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('purchase_date') border-red-500 @enderror">
                    @error('purchase_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Default hari ini. Ubah jika mencatat pembelian yang terlewat</p>
                </div>

                <!-- Input Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="purchase_price_formatted" class="block text-sm font-medium text-gray-300 mb-2">
                            Nominal Pembelian <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400 sm:text-sm">Rp</span>
                            </div>
                            <input type="text" 
                                   id="purchase_price_formatted"
                                   wire:model.live="purchase_price_formatted" 
                                   inputmode="numeric"
                                   class="block w-full pl-12 pr-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('purchase_price') border-red-500 @enderror"
                                   placeholder="250.000">
                        </div>
                        @error('purchase_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($quickAmounts as $amount)
                                <button type="button"
                                        wire:click="setAmount({{ $amount }})"
                                        class="px-3 py-1 text-xs font-medium rounded-full bg-gray-600 text-gray-200 hover:bg-blue-600 hover:text-white transition-colors">
                                    {{ number_format($amount / 1000, 0, ',', '.') }}rb
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="kwh_bought" class="block text-sm font-medium text-gray-300 mb-2">
                            kWh Didapat
                        </label>
                        <div class="relative">
                            <input type="number"
                                   id="kwh_bought"
                                   wire:model.live="kwh_bought"
                                   step="0.01"
                                   inputmode="decimal"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kwh_bought') border-red-500 @enderror"
                                   placeholder="157.32">
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

                <!-- Sisa kWh sebelum top-up -->
                <div class="bg-gray-700/50 rounded-lg p-4 border border-gray-600">
                    <label for="kwh_before_purchase" class="block text-sm font-medium text-gray-300 mb-2">
                        Sisa kWh di meteran sebelum top-up
                        <span class="text-gray-500 font-normal">(opsional)</span>
                    </label>
                    <div class="relative md:w-1/2">
                        <input type="number"
                               id="kwh_before_purchase"
                               wire:model="kwh_before_purchase"
                               step="0.01"
                               min="0"
                               inputmode="decimal"
                               class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kwh_before_purchase') border-red-500 @enderror"
                               placeholder="mis. 12.40">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-400 sm:text-sm">kWh</span>
                        </div>
                    </div>
                    @error('kwh_before_purchase')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-400">
                        Isi dengan angka yang tertera di meteran <span class="text-gray-300">tepat sebelum</span> token dimasukkan.
                        Kalau dikosongkan, sisa dihitung dari catatan terakhir &mdash; pemakaian sejak catatan itu tidak
                        diketahui, jadi hasilnya ditandai sebagai <span class="text-yellow-400">estimasi</span>.
                    </p>
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
                    {{-- wire:loading menonaktifkan tombol selama proses simpan. Tanpa ini
                         tombol diam saja saat ditekan, dan di jaringan lambat orang
                         menekannya lagi -- pembelian bisa tercatat dua kali. --}}
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold rounded-lg shadow-md transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <svg wire:loading.remove wire:target="submit" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg wire:loading wire:target="submit" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="submit">Simpan Pembelian</span>
                        <span wire:loading wire:target="submit">Menyimpan&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>