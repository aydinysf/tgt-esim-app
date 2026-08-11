@extends('layouts.app')

@section('title', 'Paket Yönetimi & Müşteriye Atama — POLO SIM')

@section('content')
<div class="space-y-8">
    <!-- Top Bar Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-box-open text-blue-400"></i>
                <span>eSIM Paketleri & Müşteriye Özel Fiyatlandırma</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">API ile çekilen paketleri listeleyin, müşterilere atayın ve satış fiyatlarını belirleyin.</p>
        </div>

        <form action="{{ route('admin.packages.sync') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-sm rounded-xl shadow-lg glow-blue transition flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-down"></i>
                <span>API'den Paketleri Çek / Yenile</span>
            </button>
        </form>
    </div>

    <!-- Section 1: Bulk / Single Customer Package Assignment Form -->
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-blue-500 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-800">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-blue-400"></i>
                    <span>Toplu Müşteri Paket Atama & Fiyatlandırma</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Tüm müşterilerinize veya seçeceğiniz müşterilere topluca paket atayabilir, alış fiyatları üzerine kâr marjı ekleyebilirsiniz.</p>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-900/80 p-1.5 rounded-xl border border-slate-800 self-start sm:self-auto">
                <button type="button" onclick="setAssignMode('bulk')" id="btnModeBulk" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white shadow transition">
                    <i class="fa-solid fa-list-check"></i> Toplu Atama
                </button>
                <button type="button" onclick="setAssignMode('single')" id="btnModeSingle" class="px-3 py-1.5 text-xs font-bold rounded-lg text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-user-tag"></i> Tekil Atama
                </button>
            </div>
        </div>

        <form action="{{ route('admin.packages.assign') }}" method="POST" id="assignForm" class="space-y-6">
            @csrf
            <input type="hidden" name="assign_mode" id="assignModeInput" value="bulk">

            <!-- Step 1: Target Customers Selection -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">1. Hedef Müşteri Seçimi</label>
                <div class="flex flex-wrap gap-4 items-center">
                    <label class="flex items-center gap-2 bg-slate-900/90 px-4 py-2.5 rounded-xl border border-slate-700/80 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_customers" value="all" checked onchange="toggleCustomerSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-white">Tüm Müşteriler (Hepsine Atansın)</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 font-mono">{{ count($customers) }} Müşteri</span>
                    </label>

                    <label class="flex items-center gap-2 bg-slate-900/90 px-4 py-2.5 rounded-xl border border-slate-700/80 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_customers" value="selected" id="targetCustSelectedRadio" onchange="toggleCustomerSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-white">Seçili Müşteriler</span>
                    </label>
                </div>

                <!-- Customer Checkboxes Container (Hidden when 'all' is selected) -->
                <div id="customerCheckboxesContainer" class="hidden pt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-3 bg-slate-900/60 rounded-xl border border-slate-800 max-h-48 overflow-y-auto">
                    @foreach($customers as $c)
                        <label class="flex items-center gap-2 p-2 hover:bg-slate-800/60 rounded-lg cursor-pointer text-xs">
                            <input type="checkbox" name="user_ids[]" value="{{ $c->id }}" {{ request()->query('customer_id') == $c->id ? 'checked' : '' }} class="rounded border-slate-700 text-blue-600 focus:ring-blue-500">
                            <span class="font-semibold text-slate-200">{{ $c->name }}</span>
                            <span class="text-slate-500 text-[11px]">({{ $c->company_name ?? 'Bireysel' }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 2: Target Packages Selection -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">2. Hedef eSIM Paket Seçimi</label>
                <div class="flex flex-wrap gap-4 items-center">
                    <label class="flex items-center gap-2 bg-slate-900/90 px-4 py-2.5 rounded-xl border border-slate-700/80 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_products" value="all" checked onchange="toggleProductSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-white">Tüm Paketler (Tüm Kataloğu At)</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 font-mono">{{ count($products) }} Paket</span>
                    </label>

                    <label class="flex items-center gap-2 bg-slate-900/90 px-4 py-2.5 rounded-xl border border-slate-700/80 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_products" value="selected" id="targetProdSelectedRadio" onchange="toggleProductSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-white">Seçili Paketler</span>
                    </label>
                </div>

                <!-- Product Checkboxes Container (Hidden when 'all' is selected) -->
                <div id="productCheckboxesContainer" class="hidden pt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-3 bg-slate-900/60 rounded-xl border border-slate-800 max-h-56 overflow-y-auto">
                    @foreach($products as $p)
                        <label class="flex items-center justify-between p-2 hover:bg-slate-800/60 rounded-lg cursor-pointer text-xs border border-transparent hover:border-slate-700">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" class="rounded border-slate-700 text-blue-600 focus:ring-blue-500">
                                <span class="font-semibold text-slate-200">{{ $p->product_name }}</span>
                            </div>
                            <span class="font-mono text-cyan-400 font-bold">₺{{ number_format($p->net_price, 2) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 3: Pricing Strategy & Calculation Rule -->
            <div class="space-y-2 pt-2 border-t border-slate-800/80">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">3. Satış Fiyatlandırma Stratejisi & Kâr Marjı</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Fiyat Belirleme Türü</label>
                        <select name="pricing_type" id="pricingTypeSelect" onchange="updatePriceLabel()" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-medium focus:outline-none focus:border-blue-500">
                            <option value="margin_percent">Alış Fiyatına % Kar Marjı Ekle (%)</option>
                            <option value="margin_fixed">Alış Fiyatına Sabit Tutarlı Kar Ekle (₺)</option>
                            <option value="fixed">Sabit Satış Fiyatı Belirle (₺)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1" id="priceValueLabel">Kâr Marjı Yüzdesi (%)</label>
                        <input type="number" step="0.01" min="0" name="price_value" id="priceValueInput" required value="30" placeholder="Örn: 30"
                            class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-bold focus:outline-none focus:border-blue-500">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2.5 px-5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold text-sm rounded-xl shadow-lg glow-emerald transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Toplu Paket Atamasını Tamamla</span>
                        </button>
                    </div>
                </div>
                <p id="pricingHint" class="text-xs text-slate-500 mt-1">Örnek: Alış fiyatı ₺100 olan pakete %30 kâr eklendiğinde müşteriye ₺130.00 fiyatla atanır.</p>
            </div>
        </form>
    </div>

    <!-- Section 2: Catalog of Available Products -->
    <div class="glass-panel p-6 rounded-2xl space-y-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-list-check text-blue-400"></i>
            <span>Canlı Paketler Katalog Tablosu</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Paket Kodu</th>
                        <th class="py-3.5 px-4 font-semibold">Paket Adı</th>
                        <th class="py-3.5 px-4 font-semibold">Ülkeler</th>
                        <th class="py-3.5 px-4 font-semibold">Alış Fiyatı (Net)</th>
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
                            <td colspan="7" class="py-8 text-center text-slate-500">Henüz paket çekilmemiş. Yukarıdaki senkronizasyon butonunu kullanabilirsiniz.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleCustomerSelection(val) {
        const container = document.getElementById('customerCheckboxesContainer');
        if (val === 'selected') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function toggleProductSelection(val) {
        const container = document.getElementById('productCheckboxesContainer');
        if (val === 'selected') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function updatePriceLabel() {
        const type = document.getElementById('pricingTypeSelect').value;
        const label = document.getElementById('priceValueLabel');
        const input = document.getElementById('priceValueInput');
        const hint = document.getElementById('pricingHint');

        if (type === 'margin_percent') {
            label.innerText = 'Kâr Marjı Yüzdesi (%)';
            input.placeholder = 'Örn: 30';
            if (!input.value || input.value == '250') input.value = '30';
            hint.innerText = 'Örnek: Alış fiyatı ₺100 olan pakete %30 kâr eklendiğinde müşteriye ₺130.00 satış fiyatıyla atanır.';
        } else if (type === 'margin_fixed') {
            label.innerText = 'Sabit Kâr Miktarı (₺)';
            input.placeholder = 'Örn: 20';
            if (input.value == '30') input.value = '20';
            hint.innerText = 'Örnek: Alış fiyatı ₺100 olan pakete ₺20 kâr eklendiğinde müşteriye ₺120.00 satış fiyatıyla atanır.';
        } else {
            label.innerText = 'Sabit Satış Fiyatı (₺)';
            input.placeholder = 'Örn: 250';
            if (input.value == '20' || input.value == '30') input.value = '250';
            hint.innerText = 'Örnek: Seçilen tüm paketlerin müşterilere olan satış fiyatı doğrudan belirlenen tutar yapılır.';
        }
    }

    function setAssignMode(mode) {
        const btnBulk = document.getElementById('btnModeBulk');
        const btnSingle = document.getElementById('btnModeSingle');
        const radCustSelected = document.getElementById('targetCustSelectedRadio');
        const radProdSelected = document.getElementById('targetProdSelectedRadio');
        document.getElementById('assignModeInput').value = mode;

        if (mode === 'single') {
            btnSingle.className = 'px-3 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white shadow transition';
            btnBulk.className = 'px-3 py-1.5 text-xs font-bold rounded-lg text-slate-400 hover:text-white transition';
            radCustSelected.checked = true;
            radProdSelected.checked = true;
            toggleCustomerSelection('selected');
            toggleProductSelection('selected');
        } else {
            btnBulk.className = 'px-3 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white shadow transition';
            btnSingle.className = 'px-3 py-1.5 text-xs font-bold rounded-lg text-slate-400 hover:text-white transition';
        }
    }
</script>
@endsection
