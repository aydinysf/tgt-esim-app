@extends('layouts.app')

@section('title', 'Paket Yönetimi & Müşteriye Atama — TGT eSIM Panel')

@section('content')
<div class="space-y-8">
    <!-- Top Bar Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-box-open text-blue-400"></i>
                <span>TGT eSIM Paketleri & Müşteriye Özel Fiyatlandırma</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">TGT firmasından API ile çekilen paketleri listeleyin, müşterilere atayın ve satış fiyatlarını belirleyin.</p>
        </div>

        <form action="{{ route('admin.packages.sync') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-sm rounded-xl shadow-lg glow-blue transition flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                <span>TGT API'den Paketleri Çek / Yenile</span>
            </button>
        </form>
    </div>

    <!-- Section 1: Customer Package Assignment Form -->
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-blue-500">
        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-tag text-blue-400"></i>
            <span>Müşteriye Paket Atama & Fiyat Belirleme</span>
        </h2>

        <form action="{{ route('admin.packages.assign') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">1. Müşteri Seçin</label>
                <select name="user_id" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500">
                    <option value="">-- Müşteri Seçiniz --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request()->query('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->company_name ?? 'Bireysel' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">2. TGT eSIM Paketi</label>
                <select id="tgtProductSelect" name="tgt_product_id" required onchange="updateProfitCalculation()" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500">
                    <option value="" data-net="0">-- Paket Seçiniz --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" data-net="{{ $p->net_price }}">
                            {{ $p->product_name }} (Alış: ₺{{ number_format($p->net_price, 2) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1.5">3. Müşteriye Satış Fiyatı (₺)</label>
                <input id="salePriceInput" type="number" step="0.01" min="0.01" name="sale_price" required placeholder="0.00" oninput="updateProfitCalculation()"
                    class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl shadow-lg glow-emerald transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-circle-plus"></i>
                    <span>Paketi Müşteriye Ata</span>
                </button>
            </div>
        </form>

        <!-- Live Estimated Profit Indicator -->
        <div id="profitPreviewBar" class="mt-4 p-3 bg-slate-900/60 rounded-xl border border-slate-800 flex items-center justify-between text-xs text-slate-400">
            <div>TGT Alış Fiyatı: <span id="netPriceSpan" class="font-semibold text-slate-200">₺0.00</span></div>
            <div>Müşteri Satış Fiyatı: <span id="salePriceSpan" class="font-semibold text-slate-200">₺0.00</span></div>
            <div class="font-bold text-sm">Tahmini Paket Başı Kâr: <span id="profitSpan" class="text-emerald-400 font-extrabold">₺0.00</span></div>
        </div>
    </div>

    <!-- Section 2: Catalog of Available TGT Products -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-list-check text-blue-400"></i>
            <span>TGT Firmasından Çekilen Canlı Paketler Katalog Tablosu</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Paket Kodu</th>
                        <th class="py-3.5 px-4 font-semibold">Paket Adı</th>
                        <th class="py-3.5 px-4 font-semibold">Ülkeler</th>
                        <th class="py-3.5 px-4 font-semibold">TGT Alış Fiyatı (Net)</th>
                        <th class="py-3.5 px-4 font-semibold">Veri Miktarı</th>
                        <th class="py-3.5 px-4 font-semibold">Kullanım Süresi</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Atandığı Müşteri</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-400 font-semibold">{{ $product->product_code }}</td>
                            <td class="py-3.5 px-4 font-medium text-white">{{ $product->product_name }}</td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($product->country_code_list ?? [], 0, 4) as $country)
                                        <span class="px-2 py-0.5 bg-slate-800 rounded text-xs font-mono text-slate-300 border border-slate-700">{{ $country }}</span>
                                    @endforeach
                                    @if(count($product->country_code_list ?? []) > 4)
                                        <span class="text-xs text-slate-500 align-middle">+{{ count($product->country_code_list) - 4 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-200">₺{{ number_format($product->net_price, 2) }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-300 border border-cyan-500/20">
                                    {{ $product->data_total }} {{ $product->data_unit }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400">{{ $product->usage_period }} Gün</td>
                            <td class="py-3.5 px-4 text-center font-semibold text-purple-400">
                                {{ $product->customer_packages_count }} Müşteri
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">TGT firmasından henüz paket çekilmemiş. Yukarıdaki senkronizasyon butonunu kullanabilirsiniz.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function updateProfitCalculation() {
        const select = document.getElementById('tgtProductSelect');
        const selectedOption = select.options[select.selectedIndex];
        const netPrice = parseFloat(selectedOption.getAttribute('data-net') || 0);
        const salePrice = parseFloat(document.getElementById('salePriceInput').value || 0);

        const profit = salePrice - netPrice;

        document.getElementById('netPriceSpan').innerText = '₺' + netPrice.toFixed(2);
        document.getElementById('salePriceSpan').innerText = '₺' + salePrice.toFixed(2);
        
        const profitSpan = document.getElementById('profitSpan');
        profitSpan.innerText = '₺' + profit.toFixed(2);
        if (profit < 0) {
            profitSpan.className = 'text-rose-400 font-extrabold';
        } else {
            profitSpan.className = 'text-emerald-400 font-extrabold';
        }
    }
</script>
@endsection
