<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Riwayat Data Listrik</h1>

    <div class="mb-4">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'purchases')" 
                        class="@if($activeTab == 'purchases') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Riwayat Pembelian
                </button>
                <button wire:click="$set('activeTab', 'checks')" 
                        class="@if($activeTab == 'checks') border-indigo-500 text-indigo-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Riwayat Pengecekan
                </button>
            </nav>
        </div>
    </div>

    @if($activeTab == 'purchases')
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Timestamp
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Purchase Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            KwH Vol.
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Price per Unit
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($purchases as $purchase)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $purchase->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($purchase->purchase_price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($purchase->kwh_bought, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($purchase->price_per_unit, 2, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $purchases->links() }}
        </div>
    </div>
    @else
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Timestamp
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            KwH Vol Check
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($checks as $check)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $check->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($check->kwh_remaining, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $checks->links() }}
        </div>
    </div>
    @endif
</div>