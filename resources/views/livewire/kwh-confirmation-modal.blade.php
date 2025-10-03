<div>
    @if($showModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 overflow-y-auto h-full w-full z-50" wire:click="closeModal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-gray-800 border border-gray-700" wire:click.stop>
            <div class="mt-3">
                <!-- Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-white">Konfirmasi Sisa Listrik</h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                @if(!$showInputField)
                <!-- Question -->
                <div class="mb-6">
                    <p class="text-gray-300 mb-2">Halo! 👋</p>
                    <p class="text-gray-300 mb-4">
                        Apakah sisa listrik Anda hari ini masih
                        <span class="font-bold text-blue-600">{{ number_format($predictedKwhValue, 2) }} kWh</span>?
                    </p>
                    @if($hoursSinceLastCheck > 0)
                    <p class="text-sm text-gray-400">Terakhir dicek {{ $hoursSinceLastCheck }} jam yang lalu</p>
                    @endif
                    @if($dailyAverage > 0 && $hoursSinceLastCheck > 0)
                    <div class="mt-2 p-3 bg-gray-700 rounded-lg text-sm text-gray-300">
                        <p class="mb-1">📊 <strong>Estimasi berdasarkan:</strong></p>
                        <ul class="ml-4 space-y-1">
                            <li>• Sisa terakhir: {{ number_format($lastKwhValue, 2) }} kWh</li>
                            <li>• Penggunaan rata-rata: {{ number_format($dailyAverage, 2) }} kWh/hari</li>
                            <li>• Estimasi penggunaan: {{ number_format($dailyAverage * ($hoursSinceLastCheck / 24), 2) }} kWh ({{ $hoursSinceLastCheck }} jam)</li>
                        </ul>
                    </div>
                    @endif
                </div>

                <!-- Buttons -->
                <div class="flex flex-col space-y-3">
                    <button wire:click="confirmYes" 
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                        Ya, Benar
                    </button>
                    <button wire:click="confirmNo" 
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                        Tidak, Sudah Berubah
                    </button>
                    <button wire:click="askLater"
                            class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                        Tanya Lagi Nanti
                    </button>
                </div>
                @else
                <!-- Input Form -->
                <div class="mb-6">
                    <p class="text-gray-300 mb-4">Masukkan sisa kWh yang benar:</p>
                    <div class="mb-4">
                        <label class="block text-gray-300 text-sm font-bold mb-2">
                            Sisa kWh Saat Ini
                        </label>
                        <input type="number"
                               wire:model="newKwhValue"
                               step="0.01"
                               class="shadow appearance-none border border-gray-600 rounded w-full py-2 px-3 bg-gray-700 text-white leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 @error('newKwhValue') border-red-500 @enderror"
                               placeholder="Contoh: 150.50">
                        @error('newKwhValue')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Input Form Buttons -->
                <div class="flex space-x-3">
                    <button wire:click="saveNewValue" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        Simpan
                    </button>
                    <button wire:click="$set('showInputField', false)"
                            class="flex-1 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                        Kembali
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>