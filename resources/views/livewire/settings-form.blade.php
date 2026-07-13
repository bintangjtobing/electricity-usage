<div class="min-h-screen bg-gray-900 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-800 rounded-xl shadow-lg p-8 border border-gray-700">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-white">Pengaturan</h2>
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>

            @if (session()->has('message'))
                <div class="mb-6 bg-green-900 border-l-4 border-green-500 p-4 rounded-lg">
                    <span class="font-medium text-green-300">{{ session('message') }}</span>
                </div>
            @endif

            <form wire:submit="save" class="space-y-8">
                <!-- Identitas Meter -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4 pb-2 border-b border-gray-700">Identitas Meter</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="meter_number" class="block text-sm font-medium text-gray-300 mb-2">No Meter</label>
                            <input type="text" id="meter_number" wire:model="meter_number"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('meter_number') border-red-500 @enderror">
                            @error('meter_number') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="owner_name" class="block text-sm font-medium text-gray-300 mb-2">Pemilik</label>
                            <input type="text" id="owner_name" wire:model="owner_name"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('owner_name') border-red-500 @enderror">
                            @error('owner_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-gray-300 mb-2">Alamat</label>
                            <textarea id="address" wire:model="address" rows="2"
                                      class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('address') border-red-500 @enderror"></textarea>
                            @error('address') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tariff_type" class="block text-sm font-medium text-gray-300 mb-2">Tarif / Daya</label>
                            <input type="text" id="tariff_type" wire:model="tariff_type" placeholder="mis. R1T 2200 VA"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('tariff_type') border-red-500 @enderror">
                            @error('tariff_type') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="price_per_unit" class="block text-sm font-medium text-gray-300 mb-2">Tarif per kWh (Rp)</label>
                            <input type="number" id="price_per_unit" wire:model="price_per_unit" step="0.01"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('price_per_unit') border-red-500 @enderror">
                            @error('price_per_unit') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Kalkulator Tarif -->
                <div class="bg-gray-700/50 rounded-lg p-6 border border-gray-600">
                    <h4 class="text-sm font-semibold text-blue-400 mb-2">Hitung tarif dari struk token</h4>
                    <p class="text-xs text-gray-400 mb-4">
                        Tarif dasar PLN belum termasuk PPJ, jadi angkanya beda dari yang benar-benar Anda bayar.
                        Isi nominal dan kWh yang tertera di struk &mdash; tarif aslinya dihitung dari situ.
                    </p>
                    @if (session()->has('calc'))
                        <p class="mb-3 text-sm text-green-400">{{ session('calc') }}</p>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label for="calc_price" class="block text-xs font-medium text-gray-400 mb-1">Nominal dibayar (Rp)</label>
                            <input type="number" id="calc_price" wire:model="calc_price" placeholder="1000000"
                                   class="block w-full px-3 py-2 border border-gray-600 bg-gray-700 text-white rounded-lg text-sm">
                            @error('calc_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="calc_kwh" class="block text-xs font-medium text-gray-400 mb-1">kWh diterima</label>
                            <input type="number" id="calc_kwh" wire:model="calc_kwh" step="0.01" placeholder="629.3"
                                   class="block w-full px-3 py-2 border border-gray-600 bg-gray-700 text-white rounded-lg text-sm">
                            @error('calc_kwh') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button type="button" wire:click="calculateRate"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                            Hitung Tarif
                        </button>
                    </div>
                </div>

                <!-- Ambang & Proyeksi -->
                <div>
                    <h3 class="text-lg font-semibold text-white mb-4 pb-2 border-b border-gray-700">Ambang &amp; Proyeksi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="threshold_hemat" class="block text-sm font-medium text-gray-300 mb-2">Batas HEMAT (kWh/hari)</label>
                            <input type="number" id="threshold_hemat" wire:model="threshold_hemat" step="0.1"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('threshold_hemat') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-400">Di bawah angka ini dianggap hemat</p>
                            @error('threshold_hemat') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="threshold_boros" class="block text-sm font-medium text-gray-300 mb-2">Batas BOROS (kWh/hari)</label>
                            <input type="number" id="threshold_boros" wire:model="threshold_boros" step="0.1"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('threshold_boros') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-400">Di atas angka ini dianggap boros</p>
                            @error('threshold_boros') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="payday_day" class="block text-sm font-medium text-gray-300 mb-2">Tanggal Gajian</label>
                            <input type="number" id="payday_day" wire:model="payday_day" min="1" max="28"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('payday_day') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-400">Dipakai untuk proyeksi sisa kWh sampai gajian</p>
                            @error('payday_day') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="low_kwh_alert" class="block text-sm font-medium text-gray-300 mb-2">Peringatan sisa rendah (kWh)</label>
                            <input type="number" id="low_kwh_alert" wire:model="low_kwh_alert" step="0.1"
                                   class="block w-full px-3 py-3 border border-gray-600 bg-gray-700 text-white rounded-lg focus:ring-2 focus:ring-blue-500 @error('low_kwh_alert') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-400">Muncul peringatan bila proyeksi di bawah angka ini</p>
                            @error('low_kwh_alert') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-700">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold rounded-lg shadow-md transition">
                        <svg wire:loading.remove wire:target="save" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg wire:loading wire:target="save" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="save">Simpan Pengaturan</span>
                        <span wire:loading wire:target="save">Menyimpan&hellip;</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
