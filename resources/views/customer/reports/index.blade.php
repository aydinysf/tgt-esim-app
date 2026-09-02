@extends('layouts.app')

@section('title', 'Satış & Ciro Raporları — POLO SIM')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-chart-pie text-emerald-600"></i>
                <span>Satış & Ciro Raporları</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Bayinize ve şubelerinize ait satış adetlerini, toplam ciroları ve en çok satan paketleri detaylı olarak analiz edin.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="glass-panel px-4 py-2.5 rounded-2xl border-l-4 border-cyan-500 flex items-center gap-3 shadow-sm bg-white">
                <div class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-base font-bold shrink-0 border border-cyan-200">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 block">Kalan Bakiye</span>
                    <div class="text-base font-black text-cyan-700 leading-tight">€{{ number_format($effectiveCustomer->balance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Revenue -->
        <div class="glass-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm border-l-4 border-emerald-500 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase tracking-wider">
                <span>Toplam Ciro</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm border border-emerald-200">
                    <i class="fa-solid fa-coins"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">€{{ number_format($totalRevenue, 2) }}</div>
                <span class="text-[11px] text-slate-400 font-medium">Seçili dönemdeki toplam satış</span>
            </div>
        </div>

        <!-- Card 2: Total Quantity -->
        <div class="glass-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm border-l-4 border-blue-500 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase tracking-wider">
                <span>Satılan eSIM</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm border border-blue-200">
                    <i class="fa-solid fa-sim-card"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">{{ number_format($totalOrders) }} <span class="text-sm font-bold text-slate-500">Adet</span></div>
                <span class="text-[11px] text-slate-400 font-medium">Başarıyla aktif edilen paket</span>
            </div>
        </div>

        <!-- Card 3: This Month -->
        <div class="glass-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm border-l-4 border-indigo-500 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase tracking-wider">
                <span>Bu Ayki Satış</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm border border-indigo-200">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">€{{ number_format($monthRevenue, 2) }}</div>
                <span class="text-[11px] font-bold text-indigo-600">{{ $monthOrders }} Adet Sipariş</span>
            </div>
        </div>

        <!-- Card 4: Today -->
        <div class="glass-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm border-l-4 border-cyan-500 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-400 font-bold uppercase tracking-wider">
                <span>Bugünkü Satış</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-sm border border-cyan-200">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-slate-900">€{{ number_format($todayRevenue, 2) }}</div>
                <span class="text-[11px] font-bold text-cyan-700">{{ $todayOrders }} Adet Sipariş</span>
            </div>
        </div>
    </div>

    <!-- Date Presets & Filters Toolbar -->
    <div class="glass-card p-4 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-3">
        <!-- Preset Date Pills -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
            <span class="text-xs font-bold text-slate-400 uppercase mr-1">Dönem:</span>
            <a href="{{ route('customer.reports.index', array_merge(request()->query(), ['preset' => 'all', 'date_from' => null, 'date_to' => null])) }}" 
                class="px-3 py-1 rounded-xl text-xs font-bold transition {{ ($datePreset ?? 'all') === 'all' && empty($dateFrom) ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Tüm Zamanlar
            </a>
            <a href="{{ route('customer.reports.index', array_merge(request()->query(), ['preset' => 'today', 'date_from' => null, 'date_to' => null])) }}" 
                class="px-3 py-1 rounded-xl text-xs font-bold transition {{ ($datePreset ?? '') === 'today' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Bugün
            </a>
            <a href="{{ route('customer.reports.index', array_merge(request()->query(), ['preset' => 'yesterday', 'date_from' => null, 'date_to' => null])) }}" 
                class="px-3 py-1 rounded-xl text-xs font-bold transition {{ ($datePreset ?? '') === 'yesterday' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Dün
            </a>
            <a href="{{ route('customer.reports.index', array_merge(request()->query(), ['preset' => 'this_week', 'date_from' => null, 'date_to' => null])) }}" 
                class="px-3 py-1 rounded-xl text-xs font-bold transition {{ ($datePreset ?? '') === 'this_week' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Bu Hafta
            </a>
            <a href="{{ route('customer.reports.index', array_merge(request()->query(), ['preset' => 'this_month', 'date_from' => null, 'date_to' => null])) }}" 
                class="px-3 py-1 rounded-xl text-xs font-bold transition {{ ($datePreset ?? '') === 'this_month' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Bu Ay
            </a>
            <a href="{{ route('customer.reports.index', array_merge(request()->query(), ['preset' => 'last_month', 'date_from' => null, 'date_to' => null])) }}" 
                class="px-3 py-1 rounded-xl text-xs font-bold transition {{ ($datePreset ?? '') === 'last_month' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Geçen Ay
            </a>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('customer.reports.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
            <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                @if(count($branches) > 0)
                    <div class="w-full sm:w-auto">
                        <select name="branch_id" onchange="this.form.submit()" class="w-full sm:w-auto px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600">
                            <option value="">-- Tüm Şubeler --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ ($selectedBranchId ?? '') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-600">
                    <span class="text-xs text-slate-400 font-bold">-</span>
                    <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-blue-600">
                    <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                        Tarih Filtrele
                    </button>
                </div>
            </div>

            @if(!empty($selectedBranchId) || !empty($dateFrom) || (($datePreset ?? 'all') !== 'all'))
                <a href="{{ route('customer.reports.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition self-end sm:self-auto">
                    Filtreleri Sıfırla
                </a>
            @endif
        </form>
    </div>

    <!-- 2-Column Analytics Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Section 1: Branch Performance Breakdown -->
        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-store text-indigo-600"></i>
                    <span>Şube Bazlı Satış Performansı</span>
                </h3>
                <span class="text-xs font-bold text-slate-400">{{ count($branchBreakdown) }} Şube</span>
            </div>

            <div class="space-y-3">
                @forelse($branchBreakdown as $bStat)
                    @php
                        $percentage = $totalRevenue > 0 ? round(($bStat->total_revenue / $totalRevenue) * 100, 1) : 0;
                    @endphp
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900">{{ $bStat->branch_name ?? 'Merkez / Genel' }}</span>
                            <div class="text-right">
                                <span class="font-black text-sm text-slate-900">€{{ number_format($bStat->total_revenue, 2) }}</span>
                                <span class="text-xs text-slate-500 font-bold block">{{ $bStat->total_sales }} Satış (%{{ $percentage }})</span>
                            </div>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ max(4, $percentage) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs font-semibold">
                        Henüz şube satışı kaydedilmemiş.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Section 2: Top Sold Packages -->
        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-fire text-amber-500"></i>
                    <span>En Çok Satan eSIM Paketleri</span>
                </h3>
                <span class="text-xs font-bold text-slate-400">Top 10</span>
            </div>

            <div class="space-y-3">
                @forelse($packageBreakdown as $pStat)
                    @php $prod = $pStat->product; @endphp
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center justify-between gap-3">
                        <div class="space-y-0.5 truncate">
                            <h4 class="font-bold text-xs text-slate-900 truncate">{{ $prod->display_name ?? ($prod->product_name ?? 'eSIM Paketi') }}</h4>
                            <span class="text-[10px] font-mono text-slate-400">{{ $prod->product_code ?? '-' }}</span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $pStat->total_sales }} Adet
                            </span>
                            <span class="text-xs font-black text-slate-900 block mt-1">€{{ number_format($pStat->total_revenue, 2) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs font-semibold">
                        Henüz paket satışı kaydedilmemiş.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Detailed Sales Log Table -->
    <div class="glass-card rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden space-y-0">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-blue-600"></i>
                <span>Detaylı Satış Listesi</span>
            </h3>
            <span class="text-xs text-slate-500 font-bold">Toplam {{ $orders->total() }} Kayıt</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50/75 border-b border-slate-200 text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="p-4">Tarih / Saat</th>
                        <th class="p-4">Sipariş No</th>
                        <th class="p-4">Satış Yapan Şube</th>
                        <th class="p-4">Paket Adı</th>
                        <th class="p-4">Tutar</th>
                        <th class="p-4">ICCID</th>
                        <th class="p-4">Durum</th>
                        <th class="p-4 text-center">QR Kod</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        @php $prod = $order->product; @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 font-semibold text-slate-700 whitespace-nowrap">
                                {{ $order->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $order->order_no ?? $order->channel_order_no }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md font-bold text-[11px] bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="fa-solid fa-store text-[9px] mr-1"></i>{{ $order->branch_name ?? 'Merkez' }}
                                </span>
                            </td>
                            <td class="p-4 font-bold text-slate-900 max-w-xs truncate">
                                {{ $prod->display_name ?? ($prod->product_name ?? 'eSIM Paketi') }}
                            </td>
                            <td class="p-4 font-black text-sm text-slate-900 whitespace-nowrap">
                                €{{ number_format($order->sale_price, 2) }}
                            </td>
                            <td class="p-4 font-mono text-[11px] text-slate-600 font-bold whitespace-nowrap">
                                {{ $order->iccid ?? '-' }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $order->order_status === 'ACTIVATED' || $order->order_status === 'INUSE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $order->order_status }}
                                </span>
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                <button onclick="showQrModal('{{ $order->qr_code }}', '{{ $order->iccid }}', '{{ $order->apple_install_url }}')" 
                                    class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-blue-200 text-center transition inline-flex items-center gap-1 active:scale-95">
                                    <i class="fa-solid fa-qrcode"></i>
                                    <span class="text-[10px] font-bold">QR</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center text-slate-400 font-semibold">
                                Seçilen filtrelere uygun satış kaydı bulunamadı.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Modal: QR Code Display -->
<div id="qrModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-sm w-full p-6 rounded-3xl shadow-2xl text-center space-y-4 relative border border-slate-200">
        <button onclick="document.getElementById('qrModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        
        <h3 class="text-lg font-bold text-slate-900 flex items-center justify-center gap-2">
            <i class="fa-solid fa-qrcode text-blue-600"></i>
            <span>eSIM QR Kodu</span>
        </h3>

        <div class="p-4 bg-white rounded-2xl mx-auto inline-block border border-slate-200 shadow-md">
            <div id="qrcodeDiv"></div>
        </div>

        <div class="text-xs text-slate-600 space-y-1">
            <p class="font-bold text-slate-800">Ayarlar > Hücresel > eSIM Ekle adımlarından taratın.</p>
            <p class="font-mono text-[11px] text-blue-600 break-all font-semibold" id="qrTextValue"></p>
        </div>

        <div class="pt-3 border-t border-slate-100 space-y-2">
            <a id="modalAppleBtn" href="#" target="_blank" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                <i class="fa-brands fa-apple text-base"></i>
                <span>iOS Direkt Yükle</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function showQrModal(qrCode, iccid, appleUrl) {
        const qrContainer = document.getElementById('qrcodeDiv');
        qrContainer.innerHTML = '';

        if (qrCode) {
            new QRCode(qrContainer, {
                text: qrCode,
                width: 200,
                height: 200,
            });
            document.getElementById('qrTextValue').innerText = qrCode;
        } else {
            qrContainer.innerHTML = '<span class="text-xs text-rose-600 font-bold">QR kod üretilemedi</span>';
        }

        const appleBtn = document.getElementById('modalAppleBtn');
        if (appleUrl && appleUrl !== 'null') {
            appleBtn.href = appleUrl;
            appleBtn.classList.remove('hidden');
        } else {
            appleBtn.classList.add('hidden');
        }

        document.getElementById('qrModal').classList.remove('hidden');
    }
</script>
@endpush
@endsection
