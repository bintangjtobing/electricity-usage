<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard Monitoring Listrik</h1>
                <p class="text-gray-600 mt-2">Monitor penggunaan listrik Anda secara real-time</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('purchase') }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Input Pembelian
                </a>
                <a href="{{ route('check') }}" 
                   class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Cek Sisa kWh
                </a>
                <button wire:click="refresh" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition duration-300 ease-in-out transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        @if($lastPurchase && $lastCheck)
        
        <!-- AI Assistant Message -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Hai Bintang & Ayu! 👋</h3>
                        <p class="text-gray-700 leading-relaxed">
                            Saya adalah assistant pribadi untuk mengecek semua penggunaan listrik token di Kos Bali kamu saat ini. 
                            Saya melihat bahwa kamu membeli token terakhir kali tanggal <span class="font-semibold">{{ $lastPurchase->created_at->format('d/m/Y') }}</span>, 
                            dengan sisa listrik (kWh) sebesar <span class="font-semibold">{{ number_format($remainingKwh, 2) }}</span>.
                        </p>
                        <p class="text-gray-700 leading-relaxed mt-3">
                            Saya merasa bahwa penggunaan harian rata-rata kamu sebesar <span class="font-semibold {{ $dailyAverage > 10 ? 'text-red-600' : ($dailyAverage > 7 ? 'text-yellow-600' : 'text-green-600') }}">{{ number_format($dailyAverage, 2) }} kWh</span>, 
                            yang berarti ini <span class="font-semibold {{ $dailyAverage > 10 ? 'text-red-600' : ($dailyAverage > 7 ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ $dailyAverage > 10 ? 'cukup boros' : ($dailyAverage > 7 ? 'standar' : 'hemat') }}</span> untukmu.
                        </p>
                        <p class="text-gray-700 leading-relaxed mt-3">
                            Dengan sisa {{ number_format($remainingKwh, 2) }} kWh, untuk sampai tanggal 10 di bulan {{ $projectionToPayday['targetMonth'] }}, 
                            itu akan bersisa sekitar <span class="font-semibold {{ $projectionToPayday['remainingKwh'] < 20 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($projectionToPayday['remainingKwh'], 2) }} kWh</span>, 
                            namun ini hanya kemungkinan perhitungannya. Yang harus kamu tau, sisa hari estimasi untuk sisa kWh tersebut, 
                            sekitar <span class="font-semibold">{{ round($remainingKwh / $dailyAverage, 0) }} hari lagi</span>.
                            @if($projectionToPayday['needToBuy'])
                            <span class="text-red-600 font-semibold">⚠️ Kemungkinan kamu perlu beli token sebelum tanggal gajian!</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Usage Chart -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-2xl p-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Grafik Penggunaan Listrik</h2>
                        <p class="text-gray-600 mt-1">Tracking sisa kWh dan pembelian token</p>
                    </div>
                    <div class="text-right">
                        <div class="inline-block px-4 py-2 rounded-lg {{ $usageIndicatorColor }}">
                            <span class="text-white font-bold">{{ $usageIndicator }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ number_format($dailyAverage, 2) }} kWh/hari</p>
                    </div>
                </div>
                <div class="relative h-96">
                    <canvas id="usageChart"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('usageChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($chartData['labels'] ?? []),
                            datasets: [{
                                label: 'Sisa kWh',
                                data: @json($chartData['kwh'] ?? []),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointBackgroundColor: 'rgb(59, 130, 246)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2
                            }, {
                                label: 'Pembelian Token (kWh)',
                                data: @json($chartData['purchases'] ?? []),
                                borderColor: 'rgb(245, 158, 11)',
                                backgroundColor: 'rgb(245, 158, 11)',
                                borderWidth: 0,
                                pointRadius: 12,
                                pointHoverRadius: 15,
                                pointBackgroundColor: 'rgb(245, 158, 11)',
                                pointBorderColor: 'rgb(217, 119, 6)',
                                pointBorderWidth: 4,
                                pointStyle: 'rectRot',
                                showLine: false,
                                order: 0
                            }, {
                                label: 'Penggunaan Harian (kWh/hari)',
                                data: @json($chartData['dailyUsage'] ?? []),
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                borderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: 'rgb(239, 68, 68)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                borderDash: [5, 5],
                                yAxisID: 'y1'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        usePointStyle: true,
                                        padding: 20,
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleFont: {
                                        size: 14,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 13
                                    },
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += context.parsed.y + ' kWh';
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    type: 'linear',
                                    position: 'left',
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Sisa kWh',
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        },
                                        color: 'rgb(59, 130, 246)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + ' kWh';
                                        },
                                        font: {
                                            size: 11
                                        },
                                        color: 'rgb(59, 130, 246)'
                                    },
                                    grid: {
                                        color: 'rgba(59, 130, 246, 0.1)'
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'right',
                                    beginAtZero: true,
                                    max: 20,
                                    title: {
                                        display: true,
                                        text: 'Penggunaan Harian (kWh/hari)',
                                        font: {
                                            size: 12,
                                            weight: 'bold'
                                        },
                                        color: 'rgb(239, 68, 68)'
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + ' kWh/hari';
                                        },
                                        font: {
                                            size: 11
                                        },
                                        color: 'rgb(239, 68, 68)'
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 11
                                        },
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                }
                            }
                        }
                    });
                }
            });

            // Re-render chart when Livewire refreshes
            Livewire.on('refresh-dashboard', () => {
                location.reload();
            });
        </script>

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Pembelian Terakhir -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Pembelian Terakhir</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $lastPurchase->created_at->format('d/m/Y') }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ $daysSinceLastPurchase }} hari yang lalu</p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Sisa Listrik -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sisa Listrik (kWh)</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($remainingKwh, 2) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Update: {{ $lastCheck->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Penggunaan Rata-rata -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Penggunaan Rata-rata</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($dailyAverage, 2) }}</p>
                        <p class="text-sm text-gray-500 mt-1">kWh/hari</p>
                    </div>
                    <div class="p-3 bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Jumlah kWh Beli Terakhir -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">kWh Beli Terakhir</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($lastPurchase->kwh_bought, 2) }}</p>
                        <p class="text-sm text-gray-500 mt-1">Rp {{ number_format($lastPurchase->purchase_price, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- kWh Terpakai -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-red-500 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">kWh Terpakai</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($kwhUsed, 2) }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ round(($kwhUsed / $lastPurchase->kwh_bought) * 100, 1) }}% dari pembelian</p>
                    </div>
                    <div class="p-3 bg-red-100 rounded-full">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Sisa Hari -->
            <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-indigo-500 hover:shadow-xl transition-shadow duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Sisa Hari (Estimasi)</h3>
                        <p class="text-3xl font-bold text-gray-900">{{ $dailyAverage > 0 ? round($remainingKwh / $dailyAverage, 0) : 0 }}</p>
                        <p class="text-sm text-gray-500 mt-1">hari lagi</p>
                    </div>
                    <div class="p-3 bg-indigo-100 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projections Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Proyeksi Bulanan -->
            <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-blue-500">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Proyeksi Bulanan
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-700 font-medium">Penggunaan 1 Bulan:</span>
                        <span class="text-xl font-bold text-gray-900">{{ number_format($monthlyProjection, 2) }} kWh</span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-700 font-medium">Biaya 1 Bulan:</span>
                        <span class="text-xl font-bold text-green-600">Rp {{ number_format($monthlyCost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-700 font-medium">Frekuensi Beli Token:</span>
                        <span class="text-xl font-bold text-blue-600">{{ number_format($tokenFrequency, 1) }}x per bulan</span>
                    </div>
                </div>
            </div>

            <!-- Analisa Bulanan -->
            <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-green-500">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Analisa Bulanan
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-700 font-medium">Estimasi Bulan Depan:</span>
                        <span class="text-xl font-bold text-gray-900">{{ number_format($nextMonthEstimate, 2) }} kWh</span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-700 font-medium">Estimasi Biaya:</span>
                        <span class="text-xl font-bold text-green-600">Rp {{ number_format($nextMonthEstimate * $lastPurchase->price_per_unit, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                        <span class="text-gray-700 font-medium">Proyeksi Sisa Bulanan:</span>
                        <span class="text-xl font-bold text-blue-600">{{ number_format(max(0, $lastPurchase->kwh_bought - $monthlyProjection), 2) }} kWh</span>
                    </div>
                </div>
            </div>
        </div>

        @else
        <!-- No Data State -->
        <div class="text-center py-16">
            <div class="bg-white rounded-xl shadow-lg p-12 max-w-md mx-auto">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.34 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Data</h3>
                <p class="text-gray-600 mb-6">Belum ada data pembelian atau pengecekan listrik. Silakan input data terlebih dahulu.</p>
                <div class="space-x-4">
                    <a href="{{ route('purchase') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-300">
                        Tambah Pembelian
                    </a>
                    <a href="{{ route('check') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition duration-300">
                        Cek Sisa
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>