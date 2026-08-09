@extends('layouts.app')

@section('title', 'Satış & Kâr Raporları — TGT eSIM Panel')

@section('content')
<div class="space-y-8">
    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
                <span>Satış & Kâr Analiz Raporu</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Hangi müşteriye hangi paketin kaç paraya satıldığı ve paket başı kâr analizleri.</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2">
            <select name="customer_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:border-blue-500">
                <option value="">-- Tüm Müşteriler --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->company_name ?? 'Bireysel' }})
                    </option>
                @endforeach
            </select>
            @if($selectedCustomerId)
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-xl border border-slate-700">Filtreyi Temizle</a>
            @endif
        </form>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="glass-card p-6 rounded-2xl">
            <div class="text-xs font-semibold text-slate-400 uppercase">Toplam Satış Cirosu</div>
            <div class="text-3xl font-extrabold text-white mt-1">₺{{ number_format($totalRevenue, 2) }}</div>
        </div>

        <div class="glass-card p-6 rounded-2xl">
            <div class="text-xs font-semibold text-slate-400 uppercase">Toplam TGT Alış Maliyeti</div>
            <div class="text-3xl font-extrabold text-slate-300 mt-1">₺{{ number_format($totalCost, 2) }}</div>
        </div>

        <div class="glass-card p-6 rounded-2xl border-l-4 border-emerald-500">
            <div class="text-xs font-semibold text-emerald-400 uppercase">Toplam Net Kâr</div>
            <div class="text-3xl font-extrabold text-emerald-400 mt-1">₺{{ number_format($totalProfit, 2) }}</div>
        </div>
    </div>

    <!-- Package Profit Breakdown Table -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-chart-column text-blue-400"></i>
            <span>Paket Bazlı Kâr Performansı</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Paket Adı</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Satış Adedi</th>
                        <th class="py-3.5 px-4 font-semibold">Toplam Ciro</th>
                        <th class="py-3.5 px-4 font-semibold">Toplam TGT Maliyeti</th>
                        <th class="py-3.5 px-4 font-semibold text-emerald-400">Toplam Kâr</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($productStats as $stat)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-medium text-white">
                                {{ $stat->product->product_name ?? 'Paket' }}
                                <div class="text-xs font-mono text-slate-500">{{ $stat->product->product_code ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-blue-400">{{ $stat->total_sales }} Adet</td>
                            <td class="py-3.5 px-4 font-semibold text-white">₺{{ number_format($stat->total_revenue, 2) }}</td>
                            <td class="py-3.5 px-4 text-slate-400">₺{{ number_format($stat->total_cost, 2) }}</td>
                            <td class="py-3.5 px-4 font-extrabold text-emerald-400">+₺{{ number_format($stat->total_profit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Orders Log -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-receipt text-purple-400"></i>
            <span>Detaylı Sipariş ve Müşteri Satış Listesi</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">TGT Sipariş No</th>
                        <th class="py-3.5 px-4 font-semibold">Müşteri</th>
                        <th class="py-3.5 px-4 font-semibold">Paket</th>
                        <th class="py-3.5 px-4 font-semibold">TGT Maliyet</th>
                        <th class="py-3.5 px-4 font-semibold">Müşteri Fiyatı</th>
                        <th class="py-3.5 px-4 font-semibold text-emerald-400">Elde Edilen Kâr</th>
                        <th class="py-3.5 px-4 font-semibold">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-300 font-semibold">{{ $order->order_no ?? $order->channel_order_no }}</td>
                            <td class="py-3.5 px-4 font-medium text-white">{{ $order->customer->name ?? 'Silinmiş Müşteri' }}</td>
                            <td class="py-3.5 px-4 text-slate-200">{{ $order->product->product_name ?? 'Paket' }}</td>
                            <td class="py-3.5 px-4 text-slate-400">₺{{ number_format($order->net_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-semibold text-white">₺{{ number_format($order->sale_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-extrabold text-emerald-400">+₺{{ number_format($order->profit, 2) }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-500">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Sipariş verisi bulunmuyor.</td>
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
