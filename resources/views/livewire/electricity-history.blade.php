<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-white mb-8">Riwayat Data Listrik</h1>

    @if (session()->has('message'))
        <div class="mb-6 bg-green-900 border-l-4 border-green-500 p-4 rounded-lg">
            <span class="font-medium text-green-300">{{ session('message') }}</span>
        </div>
    @endif

    <div class="mb-4">
        <div class="border-b border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'purchases')"
                        class="@if($activeTab == 'purchases') border-indigo-500 text-indigo-400 @else border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Riwayat Pembelian
                </button>
                <button wire:click="$set('activeTab', 'checks')"
                        class="@if($activeTab == 'checks') border-indigo-500 text-indigo-400 @else border-transparent text-gray-400 hover:text-gray-300 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Riwayat Pengecekan
                </button>
            </nav>
        </div>
    </div>

    @if($activeTab == 'purchases')
    <div class="bg-gray-800 border border-gray-700 shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">kWh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tarif / kWh</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($purchases as $purchase)
                        @if ($editingType === 'purchase' && $editingId === $purchase->id)
                            <tr class="bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <input type="date" wire:model="edit_date" max="{{ now()->format('Y-m-d') }}"
                                           style="color-scheme: dark;"
                                           class="w-full px-2 py-1 text-sm bg-gray-700 border border-gray-600 text-white rounded">
                                    @error('edit_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" wire:model="edit_price" step="1"
                                           class="w-32 px-2 py-1 text-sm bg-gray-700 border border-gray-600 text-white rounded">
                                    @error('edit_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" wire:model="edit_kwh" step="0.01"
                                           class="w-24 px-2 py-1 text-sm bg-gray-700 border border-gray-600 text-white rounded">
                                    @error('edit_kwh') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-400">dihitung ulang otomatis</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <button wire:click="saveEdit" class="text-green-400 hover:text-green-300 text-sm font-medium mr-3">Simpan</button>
                                    <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-300 text-sm">Batal</button>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ $purchase->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    Rp {{ number_format($purchase->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ number_format($purchase->kwh_bought, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                    Rp {{ number_format($purchase->price_per_unit, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if ($confirmingDeleteType === 'purchase' && $confirmingDeleteId === $purchase->id)
                                        <span class="text-xs text-gray-400 mr-2">Yakin hapus?</span>
                                        <button wire:click="delete" class="text-red-400 hover:text-red-300 text-sm font-medium mr-3">Ya, hapus</button>
                                        <button wire:click="cancelDelete" class="text-gray-400 hover:text-gray-300 text-sm">Batal</button>
                                    @else
                                        <button wire:click="editPurchase({{ $purchase->id }})" class="text-blue-400 hover:text-blue-300 text-sm font-medium mr-3">Edit</button>
                                        <button wire:click="confirmDelete('purchase', {{ $purchase->id }})" class="text-red-400 hover:text-red-300 text-sm font-medium">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $purchases->links() }}
        </div>
    </div>
    @else
    <div class="bg-gray-800 border border-gray-700 shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Sisa kWh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Sumber</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($checks as $check)
                        @if ($editingType === 'check' && $editingId === $check->id)
                            <tr class="bg-gray-700/50">
                                <td class="px-6 py-4">
                                    <input type="date" wire:model="edit_date" max="{{ now()->format('Y-m-d') }}"
                                           style="color-scheme: dark;"
                                           class="w-full px-2 py-1 text-sm bg-gray-700 border border-gray-600 text-white rounded">
                                    @error('edit_date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" wire:model="edit_remaining" step="0.01"
                                           class="w-24 px-2 py-1 text-sm bg-gray-700 border border-gray-600 text-white rounded">
                                    @error('edit_remaining') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-400">jadi pembacaan meteran</td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <button wire:click="saveEdit" class="text-green-400 hover:text-green-300 text-sm font-medium mr-3">Simpan</button>
                                    <button wire:click="cancelEdit" class="text-gray-400 hover:text-gray-300 text-sm">Batal</button>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ $check->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                    {{ number_format($check->kwh_remaining, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($check->is_estimated)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-900 text-yellow-300">Estimasi</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-900 text-green-300">Meteran</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    @if ($confirmingDeleteType === 'check' && $confirmingDeleteId === $check->id)
                                        <span class="text-xs text-gray-400 mr-2">Yakin hapus?</span>
                                        <button wire:click="delete" class="text-red-400 hover:text-red-300 text-sm font-medium mr-3">Ya, hapus</button>
                                        <button wire:click="cancelDelete" class="text-gray-400 hover:text-gray-300 text-sm">Batal</button>
                                    @else
                                        <button wire:click="editCheck({{ $check->id }})" class="text-blue-400 hover:text-blue-300 text-sm font-medium mr-3">Edit</button>
                                        <button wire:click="confirmDelete('check', {{ $check->id }})" class="text-red-400 hover:text-red-300 text-sm font-medium">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400">Belum ada data pengecekan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $checks->links() }}
        </div>
    </div>
    @endif
</div>
