@extends('layouts.app')

@section('title', 'Admin Dashboard — TGT eSIM Panel')

@section('content')
<div class="space-y-8">
    <!-- Header Title -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Yönetici Paneli</h1>
            <p class="text-slate-400 text-sm mt-1">TGT Global eSIM altyapısı ve müşteri satış özetleri</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.packages.index') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl shadow-lg glow-blue transition flex items-center gap-2">
                <i class="fa-solid fa-sync"></i>
                <span>TGT Paket Senkronizasyonu</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Toplam Müşteri -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden group hover:border-blue-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Aktif Müşteri</span>
                    <div class="text-3xl font-extrabold text-white mt-1">{{ $totalCustomers }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-slate-400 gap-1">
                <span class="text-emerald-400 font-semibold flex items-center gap-0.5"><i class="fa-solid fa-arrow-up"></i> Kayıtlı</span>
                <span>kullanıcı portföyü</span>
            </div>
        </div>

        <!-- Card 2: Toplam Ciro -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden group hover:border-indigo-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Toplam Ciro</span>
                    <div class="text-3xl font-extrabold text-white mt-1">₺{{ number_format($totalRevenue, 2) }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-slate-400 gap-1">
                <span class="text-slate-300">Net Maliyet: ₺{{ number_format($totalCost, 2) }}</span>
            </div>
        </div>

        <!-- Card 3: Toplam Kâr -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden group hover:border-emerald-500/40 transition border-emerald-500/20">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-400 uppercase tracking-wider">Net Toplam Kâr</span>
                    <div class="text-3xl font-extrabold text-emerald-400 mt-1">₺{{ number_format($totalProfit, 2) }}</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl glow-emerald">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-emerald-400 gap-1 font-medium">
                <i class="fa-solid fa-chart-line"></i>
                <span>Tüm satılan eSIM paketlerinden elde edilen kar</span>
            </div>
        </div>

        <!-- Card 4: TGT Canlı Bakiye -->
        <div class="glass-card p-6 rounded-2xl relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">TGT Hesabı Bakiyesi</span>
                    <div class="text-3xl font-extrabold text-cyan-300 mt-1">
                        ${{ number_format((float)($accountBalance['accountList'][0]['balance'] ?? 0), 2) }}
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-server"></i>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-slate-400 gap-1">
                <span class="text-cyan-400 font-semibold">TGT Sandbox / Prod</span>
                <span>canlı bakiye</span>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-blue-400"></i>
                <span>Son Siparişler ve Kâr Durumu</span>
            </h2>
            <a href="{{ route('admin.reports.index') }}" class="text-xs font-semibold text-blue-400 hover:text-blue-300 transition">
                Tüm Raporu Gör <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Sipariş No</th>
                        <th class="py-3.5 px-4 font-semibold">Müşteri</th>
                        <th class="py-3.5 px-4 font-semibold">Paket Kodu & Adı</th>
                        <th class="py-3.5 px-4 font-semibold">Alış (TGT)</th>
                        <th class="py-3.5 px-4 font-semibold">Satış (Müşteri)</th>
                        <th class="py-3.5 px-4 font-semibold text-emerald-400">Kâr Miktarı</th>
                        <th class="py-3.5 px-4 font-semibold">Tarih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-300 font-semibold">{{ $order->order_no ?? $order->channel_order_no }}</td>
                            <td class="py-3.5 px-4 font-medium text-white">{{ $order->customer->name ?? 'Müşteri Silinmiş' }}</td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-200">{{ $order->product->product_name ?? 'Paket' }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ $order->product->product_code ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">₺{{ number_format($order->net_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-semibold text-white">₺{{ number_format($order->sale_price, 2) }}</td>
                            <td class="py-3.5 px-4 font-extrabold text-emerald-400">+₺{{ number_format($order->profit, 2) }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-500">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Henüz sipariş kaydı bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
