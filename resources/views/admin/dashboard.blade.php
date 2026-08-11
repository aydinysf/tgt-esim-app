@extends('layouts.app')

@section('title', 'Admin Dashboard — POLO SIM')

@section('content')
<div class="space-y-8">
    <!-- Header Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">Yönetici Paneli</h1>
            <p class="text-slate-500 text-sm mt-1">Global eSIM altyapısı ve müşteri satış özetleri</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.packages.index') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
                <i class="fa-solid fa-sync"></i>
                <span>Paket Senkronizasyonu</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Toplam Müşteri -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden shadow-sm hover:border-blue-300 transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aktif Müşteri</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">{{ $totalCustomers }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-slate-500 gap-1 font-medium">
                <span class="text-emerald-600 font-bold flex items-center gap-0.5"><i class="fa-solid fa-arrow-up"></i> Kayıtlı</span>
                <span>kullanıcı portföyü</span>
            </div>
        </div>

        <!-- Card 2: Toplam Ciro -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden shadow-sm hover:border-indigo-300 transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Toplam Ciro</span>
                    <div class="text-3xl font-black text-slate-900 mt-1">₺{{ number_format($totalRevenue, 2) }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-slate-500 gap-1 font-medium">
                <span>Net Maliyet: ₺{{ number_format($totalCost, 2) }}</span>
            </div>
        </div>

        <!-- Card 3: Toplam Kâr -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden shadow-sm hover:border-emerald-300 transition border-l-4 border-emerald-500">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Net Toplam Kâr</span>
                    <div class="text-3xl font-black text-emerald-600 mt-1">₺{{ number_format($totalProfit, 2) }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-emerald-700 gap-1 font-bold">
                <i class="fa-solid fa-chart-line"></i>
                <span>Tüm satılan eSIM paketlerinden elde edilen kâr</span>
            </div>
        </div>

        <!-- Card 4: Canlı Bakiye -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden shadow-sm hover:border-cyan-300 transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kanal Hesabı Bakiyesi</span>
                    <div class="text-3xl font-black text-cyan-700 mt-1">
                        ${{ number_format((float)($accountBalance['accountList'][0]['balance'] ?? 0), 2) }}
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 border border-cyan-200 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-server"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-slate-500 gap-1 font-medium">
                <span class="text-cyan-700 font-bold">Canlı Bakiye</span>
                <span>kredi durumu</span>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="glass-panel p-6 rounded-2xl space-y-4 shadow-sm border border-slate-200 bg-white">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-600"></i>
                <span>Son Siparişler ve Kâr Durumu</span>
            </h2>
            <a href="{{ route('admin.reports.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                Tüm Raporu Gör <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Sipariş No</th>
                        <th class="py-3.5 px-4 font-bold">Müşteri</th>
                        <th class="py-3.5 px-4 font-bold">Paket Kodu & Adı</th>
                        <th class="py-3.5 px-4 font-bold">Alış (Net)</th>
                        <th class="py-3.5 px-4 font-bold">Satış (Müşteri)</th>
                        <th class="py-3.5 px-4 font-bold text-emerald-700">Kâr Miktarı</th>
                        <th class="py-3.5 px-4 font-bold">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-700 font-bold">{{ $order->order_no ?? $order->channel_order_no }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $order->customer->name ?? 'Müşteri Silinmiş' }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-800">{{ $order->product->product_name ?? 'Paket' }}</div>
                                <div class="text-xs text-slate-400 font-mono">{{ $order->product->product_code ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-medium">₺{{ number_format($order->net_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">₺{{ number_format($order->sale_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-black text-emerald-600">+₺{{ number_format($order->profit, 2) }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-500 font-medium">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 font-medium">Henüz sipariş kaydı bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
