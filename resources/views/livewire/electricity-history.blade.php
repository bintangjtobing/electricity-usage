@php
    $totals = $calendar['totals'];
    $roles = $calendar['roles'];

    // Rp 500.000 -> "500rb", Rp 1.000.000 -> "1jt": muat di sel kalender.
    $shortRp = function (float $value): string {
        if ($value >= 1000000) {
            return rtrim(rtrim(number_format($value / 1000000, 1, ',', '.'), '0'), ',') . 'jt';
        }

        return number_format(round($value / 1000), 0, ',', '.') . 'rb';
    };

    // Ambang dari Pengaturan, sama dengan label hemat/standar/boros di dashboard.
    $tone = fn (?float $rate) => match (true) {
        $rate === null => 'gray',
        $rate > $thresholdBoros => 'red',
        $rate >= $thresholdHemat => 'yellow',
        default => 'green',
    };
    $toneDot = ['green' => 'bg-green-400', 'yellow' => 'bg-yellow-400', 'red' => 'bg-red-400', 'gray' => 'bg-gray-400'];
    $toneText = ['green' => 'text-green-400', 'yellow' => 'text-yellow-400', 'red' => 'text-red-400', 'gray' => 'text-gray-300'];
    $toneLabel = ['green' => 'hemat', 'yellow' => 'standar', 'red' => 'boros', 'gray' => ''];

    $icon = fn (string $path, string $class = 'w-5 h-5') => '<svg class="' . $class . ' shrink-0" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' . $path . '"/></svg>';
    $paths = [
        'clock' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
        'money' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
        'bolt' => 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
        'gauge' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
        'info' => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
        'pencil' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125',
        'trash' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
        'close' => 'M6 18L18 6M6 6l12 12',
    ];

    $inputClass = 'mt-1 block w-full px-3 py-2 text-sm bg-gray-900 border border-gray-600 text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500';
    $iconButton = 'p-2 rounded-full text-gray-400 hover:text-white hover:bg-gray-700 transition-colors';
@endphp

{{-- Popover ala Google Calendar: konten tiap badge sudah dirender di server,
     Alpine cukup memilih mana yang tampil dan menaruhnya di samping badge.
     Edit/Hapus tetap lewat Livewire. --}}
<div class="container mx-auto px-4 py-8 relative"
     x-data="{
        open: null,
        style: '',
        show(key, el) {
            if (this.open === key) { this.close(); return; }
            this.cancelPending();
            this.open = key;
            this.$nextTick(() => this.place(el));
        },
        place(el) {
            // Di HP popover jadi bottom sheet (diatur lewat kelas CSS).
            if (window.innerWidth < 640) { this.style = ''; return; }
            const root = this.$root.getBoundingClientRect();
            const chip = el.getBoundingClientRect();
            const width = 360, gap = 8, pad = 12;
            const height = this.$refs.popover.offsetHeight;
            let left = chip.right + gap;
            if (left + width > window.innerWidth - pad) left = chip.left - width - gap;
            if (left < pad) left = Math.max(pad, (window.innerWidth - width) / 2);
            let top = chip.top - 8;
            if (top + height > window.innerHeight - pad) top = Math.max(pad, window.innerHeight - height - pad);
            this.style = `left:${left - root.left}px;top:${top - root.top}px`;
        },
        cancelPending() {
            if (this.$wire.editingId) this.$wire.cancelEdit();
            if (this.$wire.confirmingDeleteId) this.$wire.cancelDelete();
        },
        close() {
            this.open = null;
            this.cancelPending();
        },
     }"
     x-on:keydown.escape.window="close()"
     x-on:history-updated.window="open = null">

    <h1 class="text-3xl font-bold text-white mb-6">Riwayat Data Listrik</h1>

    @if (session()->has('message'))
        <div class="mb-6 bg-green-900 border-l-4 border-green-500 p-4 rounded-lg">
            <span class="font-medium text-green-300">{{ session('message') }}</span>
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 mb-4">
        <button type="button" wire:click="goToToday" x-on:click="open = null"
                class="px-4 py-2 rounded-full border border-gray-600 text-sm font-medium text-gray-200 hover:bg-gray-800 transition-colors">
            Hari ini
        </button>
        <div class="flex items-center">
            <button type="button" wire:click="previousMonth" x-on:click="open = null" aria-label="Bulan sebelumnya" class="{{ $iconButton }}">
                {!! $icon('M15.75 19.5L8.25 12l7.5-7.5') !!}
            </button>
            <button type="button" wire:click="nextMonth" x-on:click="open = null" aria-label="Bulan berikutnya" class="{{ $iconButton }}">
                {!! $icon('M8.25 4.5l7.5 7.5-7.5 7.5') !!}
            </button>
        </div>
        <h2 class="text-xl sm:text-2xl font-semibold text-white">{{ $calendar['label'] }}</h2>
        <span wire:loading class="text-xs text-gray-500">memuat&hellip;</span>

        <div class="w-full lg:w-auto lg:ml-auto flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-400">
            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-2.5 rounded-sm bg-emerald-600"></span>Beli token</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-400"></span>Sisa meteran</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span>Sisa estimasi</span>
            <span class="inline-flex items-center gap-1.5">
                <span class="flex gap-0.5"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span><span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span></span>
                Total terpakai (hemat/standar/boros)
            </span>
        </div>
    </div>

    {{-- Ringkasan bulan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4">
            <p class="text-xs uppercase tracking-wide text-gray-400">Pembelian</p>
            <p class="mt-1 text-xl sm:text-2xl font-bold text-white">Rp {{ number_format($totals['purchasePrice'], 0, ',', '.') }}</p>
            <p class="mt-0.5 text-xs sm:text-sm text-gray-400">{{ $totals['purchaseCount'] }}x &middot; {{ number_format($totals['purchaseKwh'], 2) }} kWh</p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4">
            <p class="text-xs uppercase tracking-wide text-gray-400">Total terpakai</p>
            @if ($totals['dailyAverage'] !== null)
                <p class="mt-1 text-xl sm:text-2xl font-bold text-white">{{ number_format($totals['usage'], 2) }} <span class="text-sm font-medium text-gray-400">kWh</span></p>
            @else
                <p class="mt-1 text-xl sm:text-2xl font-bold text-gray-500">&ndash;</p>
            @endif
            <p class="mt-0.5 text-xs sm:text-sm text-gray-400">
                @if ($totals['dailyAverage'] !== null)
                    <span class="{{ $toneText[$tone($totals['dailyAverage'])] }}">{{ number_format($totals['dailyAverage'], 2) }} kWh/hari</span>
                    &middot; {{ number_format($totals['coveredDays'], 1) }} hari tercatat
                @else
                    Belum ada bacaan meteran
                @endif
            </p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4">
            <p class="text-xs uppercase tracking-wide text-gray-400">Pengecekan</p>
            <p class="mt-1 text-xl sm:text-2xl font-bold text-white">{{ $totals['checkCount'] }}<span class="text-sm font-medium text-gray-400">x</span></p>
            <p class="mt-0.5 text-xs sm:text-sm text-gray-400">
                {{ $totals['estimatedCount'] > 0 ? $totals['estimatedCount'] . ' di antaranya estimasi' : 'semua dari meteran' }}
            </p>
        </div>
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4">
            <p class="text-xs uppercase tracking-wide text-gray-400">Sisa terakhir</p>
            @if ($totals['lastCheck'])
                <p class="mt-1 text-xl sm:text-2xl font-bold text-white">{{ $totals['lastCheck']->is_estimated ? '~' : '' }}{{ number_format($totals['lastCheck']->kwh_remaining, 2) }} <span class="text-sm font-medium text-gray-400">kWh</span></p>
                <p class="mt-0.5 text-xs sm:text-sm text-gray-400">{{ $totals['lastCheck']->created_at->copy()->locale('id')->translatedFormat('j M, H:i') }}</p>
            @else
                <p class="mt-1 text-xl sm:text-2xl font-bold text-gray-500">&ndash;</p>
                <p class="mt-0.5 text-xs sm:text-sm text-gray-500">tidak ada pengecekan</p>
            @endif
        </div>
    </div>

    {{-- Kalender --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
        <div class="grid grid-cols-7 border-b border-gray-700">
            @foreach ($weekdays as $weekday)
                <div class="py-2 text-center text-[10px] sm:text-xs font-medium tracking-wide {{ $loop->first ? 'text-red-400' : 'text-gray-400' }}">{{ $weekday }}</div>
            @endforeach
        </div>

        @foreach ($calendar['weeks'] as $week)
            <div class="grid grid-cols-7 border-b border-gray-700 last:border-b-0" wire:key="week-{{ $week[0]['key'] }}">
                @foreach ($week as $day)
                    @php
                        $lastCheck = $day['checks']->last();
                        $dayTone = $tone($day['rate']);
                        $partial = $day['usage'] !== null && $day['coveredSeconds'] < 86400 - 60;
                    @endphp
                    <div class="min-h-[84px] sm:min-h-[118px] p-0.5 sm:p-1.5 border-r border-gray-700 last:border-r-0 {{ $day['inMonth'] ? '' : 'bg-gray-900/50' }}"
                         wire:key="cell-{{ $day['key'] }}">
                        <div class="flex justify-center mb-1">
                            <span class="inline-flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full text-xs sm:text-sm
                                {{ $day['isToday'] ? 'bg-blue-500 text-white font-semibold' : ($day['inMonth'] ? 'text-gray-200' : 'text-gray-600') }}">
                                {{ $day['date']->day }}
                            </span>
                        </div>

                        <div class="space-y-0.5 sm:space-y-1 {{ $day['inMonth'] ? '' : 'opacity-60' }}">
                            @if ($day['purchases']->isNotEmpty())
                                <button type="button"
                                        x-on:click.stop="show('{{ $day['key'] }}-beli', $el)"
                                        :class="open === '{{ $day['key'] }}-beli' && 'ring-2 ring-white/70'"
                                        aria-label="Pembelian {{ $day['date']->copy()->locale('id')->translatedFormat('j F') }}"
                                        class="w-full rounded px-1 sm:px-1.5 py-0.5 text-left text-[10px] sm:text-xs font-semibold leading-tight text-white bg-emerald-600 hover:bg-emerald-500 truncate transition-colors">
                                    <span class="hidden sm:inline">Beli </span>{{ $shortRp($day['purchases']->sum('purchase_price')) }}@if ($day['purchases']->count() > 1)<span class="font-normal opacity-80"> &times;{{ $day['purchases']->count() }}</span>@endif
                                </button>
                            @endif

                            @if ($lastCheck)
                                <button type="button"
                                        x-on:click.stop="show('{{ $day['key'] }}-sisa', $el)"
                                        :class="open === '{{ $day['key'] }}-sisa' && 'bg-gray-700'"
                                        aria-label="Sisa meteran {{ $day['date']->copy()->locale('id')->translatedFormat('j F') }}"
                                        class="w-full flex items-center gap-1 rounded px-1 sm:px-1.5 py-0.5 text-left text-[10px] sm:text-xs leading-tight text-gray-200 hover:bg-gray-700 transition-colors">
                                    <span class="shrink-0 w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full {{ $lastCheck->is_estimated ? 'bg-amber-400' : 'bg-sky-400' }}"></span>
                                    <span class="truncate">
                                        <span class="hidden sm:inline">Sisa {{ $lastCheck->is_estimated ? '~' : '' }}{{ number_format($lastCheck->kwh_remaining, 2) }}</span>
                                        <span class="sm:hidden">{{ number_format($lastCheck->kwh_remaining, 0) }}</span>
                                    </span>
                                </button>
                            @endif

                            @if ($day['usage'] !== null)
                                <button type="button"
                                        x-on:click.stop="show('{{ $day['key'] }}-total', $el)"
                                        :class="open === '{{ $day['key'] }}-total' && 'bg-gray-700'"
                                        aria-label="Total pemakaian {{ $day['date']->copy()->locale('id')->translatedFormat('j F') }}"
                                        class="w-full flex items-center gap-1 rounded px-1 sm:px-1.5 py-0.5 text-left text-[10px] sm:text-xs leading-tight text-gray-300 hover:bg-gray-700 transition-colors {{ $partial ? 'italic' : '' }}">
                                    <span class="shrink-0 w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full {{ $toneDot[$dayTone] }}"></span>
                                    <span class="truncate">
                                        <span class="hidden sm:inline">Total </span>{{ number_format($day['usage'], 1) }}<span class="hidden sm:inline"> kWh</span>
                                    </span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <p class="mt-3 text-xs text-gray-500">
        Total = perkiraan kWh terpakai per tanggal: pemakaian di antara dua bacaan meteran dibagi rata per jam.
        Miring = hari yang baru tercatat sebagian. Klik badge untuk detail, edit, atau hapus.
    </p>

    {{-- Latar gelap untuk bottom sheet di HP --}}
    <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-black/60 sm:hidden" x-on:click="close()"></div>

    {{-- Popover detail --}}
    <div x-show="open" x-cloak x-ref="popover"
         x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-on:click.outside="close()"
         :style="style"
         role="dialog" aria-modal="true"
         class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] overflow-y-auto pb-[env(safe-area-inset-bottom)] rounded-t-2xl sm:absolute sm:inset-x-auto sm:bottom-auto sm:w-[360px] sm:max-h-none sm:rounded-2xl bg-gray-800 border border-gray-700 shadow-2xl shadow-black/60">
        <div class="sm:hidden mx-auto mt-2 h-1 w-10 rounded-full bg-gray-600"></div>

        @foreach ($calendar['weeks'] as $week)
            @foreach ($week as $day)
                @php
                    $dateLabel = $day['date']->copy()->locale('id')->translatedFormat('l, j F Y');
                @endphp

                {{-- Pembelian --}}
                @if ($day['purchases']->isNotEmpty())
                    <div x-show="open === '{{ $day['key'] }}-beli'" wire:key="pop-{{ $day['key'] }}-beli" class="p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="mt-1.5 w-4 h-4 rounded bg-emerald-600 shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg sm:text-xl text-white">Pembelian Token</h3>
                                <p class="text-sm text-gray-400">{{ $dateLabel }}</p>
                            </div>
                            <button type="button" x-on:click="close()" aria-label="Tutup" class="{{ $iconButton }} -mt-1 -mr-2">{!! $icon($paths['close']) !!}</button>
                        </div>

                        @foreach ($day['purchases'] as $purchase)
                            @php
                                $near = $day['checks']->filter(fn ($c) => abs($purchase->created_at->diffInSeconds($c->created_at, false)) <= \App\Models\ElectricityPurchase::ATTACHED_CHECK_SECONDS);
                                $before = $near->first(fn ($c) => $c->created_at->lt($purchase->created_at));
                                $after = $near->first(fn ($c) => $c->created_at->gte($purchase->created_at));
                                $isEditing = $editingType === 'purchase' && $editingId === $purchase->id;
                                $isDeleting = $confirmingDeleteType === 'purchase' && $confirmingDeleteId === $purchase->id;
                            @endphp
                            <div class="mt-4 {{ $loop->first ? '' : 'pt-4 border-t border-gray-700' }}" wire:key="purchase-{{ $purchase->id }}">
                                @if ($isEditing)
                                    <div class="space-y-3">
                                        <label class="block text-xs text-gray-400">Tanggal
                                            <input type="date" wire:model="edit_date" max="{{ now()->format('Y-m-d') }}" style="color-scheme: dark;" class="{{ $inputClass }}">
                                        </label>
                                        @error('edit_date') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                        <div class="grid grid-cols-2 gap-3">
                                            <label class="block text-xs text-gray-400">Nominal (Rp)
                                                <input type="text" inputmode="numeric" autocomplete="off" wire:model="edit_price" class="{{ $inputClass }}">
                                            </label>
                                            <label class="block text-xs text-gray-400">kWh
                                                <input type="text" inputmode="decimal" autocomplete="off" wire:model="edit_kwh" class="{{ $inputClass }}">
                                            </label>
                                        </div>
                                        @error('edit_price') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                        @error('edit_kwh') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                        <p class="text-xs text-gray-500">Tarif dihitung ulang otomatis. Kalau tanggal diubah, titik sisa sebelum/sesudah top-up ikut pindah.</p>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-sm rounded-full text-gray-300 hover:bg-gray-700">Batal</button>
                                            <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium rounded-full bg-blue-600 hover:bg-blue-500 text-white disabled:opacity-60">Simpan</button>
                                        </div>
                                    </div>
                                @else
                                    <div class="space-y-2.5 text-sm text-gray-200">
                                        <div class="flex items-center gap-3">
                                            <span class="text-gray-400">{!! $icon($paths['clock']) !!}</span>
                                            <span class="flex-1">Pukul {{ $purchase->created_at->format('H:i') }}</span>
                                            <button type="button" wire:click="editPurchase({{ $purchase->id }})" aria-label="Edit pembelian" class="{{ $iconButton }}">{!! $icon($paths['pencil'], 'w-4 h-4') !!}</button>
                                            <button type="button" wire:click="confirmDelete('purchase', {{ $purchase->id }})" aria-label="Hapus pembelian" class="{{ $iconButton }} hover:text-red-400">{!! $icon($paths['trash'], 'w-4 h-4') !!}</button>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-gray-400">{!! $icon($paths['money']) !!}</span>
                                            <span class="text-base font-semibold text-white">Rp {{ number_format($purchase->purchase_price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-gray-400">{!! $icon($paths['bolt']) !!}</span>
                                            <span>{{ number_format($purchase->kwh_bought, 2) }} kWh <span class="text-gray-400">&middot; Rp {{ number_format($purchase->price_per_unit, 2, ',', '.') }}/kWh</span></span>
                                        </div>
                                        @if ($before || $after)
                                            <div class="flex items-start gap-3">
                                                <span class="text-gray-400">{!! $icon($paths['gauge']) !!}</span>
                                                <div>
                                                    @if ($before)
                                                        <p>Sisa {{ number_format($before->kwh_remaining, 2) }} &rarr; {{ number_format(optional($after)->kwh_remaining ?? $before->kwh_remaining + $purchase->kwh_bought, 2) }} kWh</p>
                                                        <p class="text-xs text-gray-400">sebelum &rarr; sesudah top-up</p>
                                                    @else
                                                        <p>Saldo sesudah ~{{ number_format($after->kwh_remaining, 2) }} kWh</p>
                                                        <p class="text-xs text-amber-300/90">Estimasi: sisa sebelum beli tidak diisi</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($isDeleting)
                                        <div class="mt-3 p-3 rounded-lg bg-red-950/50 border border-red-900">
                                            <p class="text-sm text-red-200">Hapus pembelian ini?</p>
                                            @if ($near->isNotEmpty())
                                                <p class="mt-1 text-xs text-red-300/80">Titik sisa sebelum/sesudah top-up tidak ikut terhapus; hapus lewat badge Sisa kalau perlu.</p>
                                            @endif
                                            <div class="mt-2 flex justify-end gap-2">
                                                <button type="button" wire:click="cancelDelete" class="px-3 py-1.5 text-sm rounded-full text-gray-300 hover:bg-gray-700">Batal</button>
                                                <button type="button" wire:click="delete" class="px-3 py-1.5 text-sm font-medium rounded-full bg-red-600 hover:bg-red-500 text-white">Ya, hapus</button>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Sisa meteran --}}
                @if ($day['checks']->isNotEmpty())
                    <div x-show="open === '{{ $day['key'] }}-sisa'" wire:key="pop-{{ $day['key'] }}-sisa" class="p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="mt-1.5 w-4 h-4 rounded-full bg-sky-400 shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg sm:text-xl text-white">Sisa Meteran</h3>
                                <p class="text-sm text-gray-400">{{ $dateLabel }} &middot; {{ $day['checks']->count() }} catatan</p>
                            </div>
                            <button type="button" x-on:click="close()" aria-label="Tutup" class="{{ $iconButton }} -mt-1 -mr-2">{!! $icon($paths['close']) !!}</button>
                        </div>

                        <div class="mt-3 divide-y divide-gray-700">
                            @foreach ($day['checks'] as $check)
                                @php
                                    $isEditing = $editingType === 'check' && $editingId === $check->id;
                                    $isDeleting = $confirmingDeleteType === 'check' && $confirmingDeleteId === $check->id;
                                @endphp
                                <div class="py-3" wire:key="check-{{ $check->id }}">
                                    @if ($isEditing)
                                        <div class="space-y-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <label class="block text-xs text-gray-400">Tanggal
                                                    <input type="date" wire:model="edit_date" max="{{ now()->format('Y-m-d') }}" style="color-scheme: dark;" class="{{ $inputClass }}">
                                                </label>
                                                <label class="block text-xs text-gray-400">Sisa kWh
                                                    <input type="text" inputmode="decimal" autocomplete="off" wire:model="edit_remaining" class="{{ $inputClass }}">
                                                </label>
                                            </div>
                                            @error('edit_date') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                            @error('edit_remaining') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
                                            <p class="text-xs text-gray-500">Disimpan sebagai pembacaan meteran (bukan estimasi).</p>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" wire:click="cancelEdit" class="px-4 py-2 text-sm rounded-full text-gray-300 hover:bg-gray-700">Batal</button>
                                                <button type="button" wire:click="saveEdit" wire:loading.attr="disabled" class="px-4 py-2 text-sm font-medium rounded-full bg-blue-600 hover:bg-blue-500 text-white disabled:opacity-60">Simpan</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3">
                                            <span class="w-12 text-sm text-gray-400 tabular-nums">{{ $check->created_at->format('H:i') }}</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-base font-semibold text-white tabular-nums">{{ number_format($check->kwh_remaining, 2) }} <span class="text-sm font-normal text-gray-400">kWh</span></p>
                                                <p class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs">
                                                    @if ($check->is_estimated)
                                                        <span class="px-2 py-0.5 rounded-full bg-amber-900/70 text-amber-300">Estimasi</span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full bg-sky-900/70 text-sky-300">Meteran</span>
                                                    @endif
                                                    @isset($roles[$check->id])
                                                        <span class="text-gray-400">{{ $roles[$check->id] }}</span>
                                                    @endisset
                                                </p>
                                            </div>
                                            <button type="button" wire:click="editCheck({{ $check->id }})" aria-label="Edit sisa" class="{{ $iconButton }}">{!! $icon($paths['pencil'], 'w-4 h-4') !!}</button>
                                            <button type="button" wire:click="confirmDelete('check', {{ $check->id }})" aria-label="Hapus sisa" class="{{ $iconButton }} hover:text-red-400">{!! $icon($paths['trash'], 'w-4 h-4') !!}</button>
                                        </div>

                                        @if ($isDeleting)
                                            <div class="mt-3 p-3 rounded-lg bg-red-950/50 border border-red-900">
                                                <p class="text-sm text-red-200">Hapus catatan sisa {{ number_format($check->kwh_remaining, 2) }} kWh ini?</p>
                                                <div class="mt-2 flex justify-end gap-2">
                                                    <button type="button" wire:click="cancelDelete" class="px-3 py-1.5 text-sm rounded-full text-gray-300 hover:bg-gray-700">Batal</button>
                                                    <button type="button" wire:click="delete" class="px-3 py-1.5 text-sm font-medium rounded-full bg-red-600 hover:bg-red-500 text-white">Ya, hapus</button>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Total pemakaian --}}
                @if ($day['usage'] !== null)
                    @php
                        $dayTone = $tone($day['rate']);
                        $coveredHours = $day['coveredSeconds'] / 3600;
                    @endphp
                    <div x-show="open === '{{ $day['key'] }}-total'" wire:key="pop-{{ $day['key'] }}-total" class="p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <span class="mt-1.5 w-4 h-4 rounded-full {{ $toneDot[$dayTone] }} shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg sm:text-xl text-white">Total Pemakaian</h3>
                                <p class="text-sm text-gray-400">{{ $dateLabel }}</p>
                            </div>
                            <button type="button" x-on:click="close()" aria-label="Tutup" class="{{ $iconButton }} -mt-1 -mr-2">{!! $icon($paths['close']) !!}</button>
                        </div>

                        <div class="mt-4 space-y-3 text-sm text-gray-200">
                            <div class="flex items-center gap-3">
                                <span class="text-gray-400">{!! $icon($paths['bolt']) !!}</span>
                                <div>
                                    <p><span class="text-2xl font-bold text-white tabular-nums">&asymp; {{ number_format($day['usage'], 2) }}</span> <span class="text-gray-400">kWh terpakai</span></p>
                                    @if ($coveredHours < 23.98)
                                        <p class="text-xs text-gray-400">
                                            {{ $day['isToday'] ? 'Baru tercatat' : 'Hanya tercatat' }} {{ number_format($coveredHours, 1) }} jam &middot; laju
                                            <span class="{{ $toneText[$dayTone] }}">{{ number_format($day['rate'], 2) }} kWh/hari ({{ $toneLabel[$dayTone] }})</span>
                                        </p>
                                    @else
                                        <p class="text-xs {{ $toneText[$dayTone] }}">tergolong {{ $toneLabel[$dayTone] }}</p>
                                    @endif
                                </div>
                            </div>

                            @if (count($day['intervals']) > 0)
                                <div class="flex items-start gap-3">
                                    <span class="text-gray-400">{!! $icon($paths['gauge']) !!}</span>
                                    <div class="space-y-2">
                                        <p class="text-xs text-gray-400">Dihitung dari bacaan meteran:</p>
                                        @foreach ($day['intervals'] as $interval)
                                            @php $days = $interval['seconds'] / 86400; @endphp
                                            <div>
                                                <p class="tabular-nums">{{ $interval['from']->created_at->copy()->locale('id')->translatedFormat('j M H:i') }} &rarr; {{ $interval['to']->created_at->copy()->locale('id')->translatedFormat('j M H:i') }}</p>
                                                <p class="text-xs text-gray-400 tabular-nums">{{ number_format($interval['usage'], 2) }} kWh dalam {{ number_format($days, 1) }} hari &middot; {{ number_format($interval['usage'] / $days, 2) }} kWh/hari</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-start gap-3 text-xs text-gray-400">
                                <span>{!! $icon($paths['info'], 'w-4 h-4') !!}</span>
                                <p>Pemakaian di antara dua bacaan meteran dibagi rata per jam lalu dijumlah per tanggal. Catatan estimasi dilewati.</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endforeach
    </div>
</div>
