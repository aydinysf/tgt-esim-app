@extends('layouts.app')

@section('title', 'Satış & Kâr Raporları — POLO SIM')

@section('content')
<div class="space-y-8">
    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <span>Satış & Kâr Analiz Raporu</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">Hangi müşteriye hangi paketin kaç paraya satıldığı ve paket başı kâr analizleri.</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
            <select name="customer_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm font-bold focus:outline-none focus:border-blue-600 shadow-sm">
                <option value="">-- Tüm Müşteriler --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->company_name ?? 'Bireysel' }})
                    </option>
                @endforeach
            </select>

            @if($selectedCustomerId && count($availableBranches) > 0)
                <select name="branch_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm font-bold focus:outline-none focus:border-blue-600 shadow-sm">
                    <option value="">-- Tüm Şubeler --</option>
                    @foreach($availableBranches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            @if($selectedCustomerId || $selectedBranchId)
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs rounded-xl border border-slate-300 font-bold">Filtreyi Temizle</a>
            @endif
        </form>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase">Toplam Satış Cirosu</div>
            <div class="text-3xl font-black text-slate-900 mt-1">€{{ number_format($totalRevenue, 2) }}</div>
        </div>

        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase">Toplam Alış Maliyeti</div>
            <div class="text-3xl font-black text-slate-700 mt-1">€{{ number_format($totalCost, 2) }}</div>
        </div>

        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm border-l-4 border-emerald-500">
            <div class="text-xs font-bold text-emerald-700 uppercase">Toplam Net Kâr</div>
            <div class="text-3xl font-black text-emerald-600 mt-1">€{{ number_format($totalProfit, 2) }}</div>
        </div>
    </div>

    <!-- Package Profit Breakdown Table -->
    <div class="glass-panel p-6 rounded-2xl space-y-4 bg-white border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-chart-column text-blue-600"></i>
            <span>Paket Bazlı Kâr Performansı</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Paket Adı</th>
                        <th class="py-3.5 px-4 font-bold text-center">Satış Adedi</th>
                        <th class="py-3.5 px-4 font-bold">Toplam Ciro</th>
                        <th class="py-3.5 px-4 font-bold">Toplam Maliyet</th>
                        <th class="py-3.5 px-4 font-bold text-emerald-700">Toplam Kâr</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productStats as $stat)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $stat->product->product_name ?? 'Paket' }}
                                <div class="text-xs font-mono text-slate-400 font-medium">{{ $stat->product->product_code ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-black text-blue-700">{{ $stat->total_sales }} Adet</td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">€{{ number_format($stat->total_revenue, 2) }}</td>
                            <td class="py-3.5 px-4 text-slate-500 font-medium">€{{ number_format($stat->total_cost, 2) }}</td>
                            <td class="py-3.5 px-4 font-black text-emerald-600">+€{{ number_format($stat->total_profit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 font-medium">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Orders Log -->
    <div class="glass-panel p-6 rounded-2xl space-y-4 bg-white border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-receipt text-purple-600"></i>
            <span>Detaylı Sipariş ve Müşteri Satış Listesi</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Sipariş No</th>
                        <th class="py-3.5 px-4 font-bold">Müşteri</th>
                        <th class="py-3.5 px-4 font-bold">Şube / Bayi</th>
                        <th class="py-3.5 px-4 font-bold">Paket</th>
                        <th class="py-3.5 px-4 font-bold">Maliyet (Net)</th>
                        <th class="py-3.5 px-4 font-bold">Müşteri Fiyatı</th>
                        <th class="py-3.5 px-4 font-bold text-emerald-700">Elde Edilen Kâr</th>
                        <th class="py-3.5 px-4 font-bold">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-700 font-bold">{{ $order->order_no ?? $order->channel_order_no }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $order->customer->name ?? 'Silinmiş Müşteri' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-md font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="fa-solid fa-store text-[10px] mr-1"></i>{{ $order->branch_name ?? 'Merkez / Genel' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-800 font-bold">{{ $order->product->product_name ?? 'Paket' }}</td>
                            <td class="py-3.5 px-4 text-slate-500 font-medium">€{{ number_format($order->net_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">€{{ number_format($order->sale_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-black text-emerald-600">+€{{ number_format($order->profit, 2) }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-500 font-medium">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 font-medium">Sipariş verisi bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
