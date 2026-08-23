@extends('layouts.app')

@section('title', 'Paket Yönetimi & Müşteriye Atama — POLO SIM')

@section('content')
<div class="space-y-8">
    <!-- Top Bar Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-box-open text-blue-600"></i>
                <span>eSIM Paketleri & Müşteriye Özel Fiyatlandırma</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">API ile çekilen paketleri listeleyin, müşterilere atayın ve satış fiyatlarını belirleyin.</p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('admin.packages.sync') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl border border-slate-300 transition flex items-center gap-2 active:scale-95" title="Tüm kataloğu çek">
                    <i class="fa-solid fa-cloud-arrow-down text-blue-600"></i>
                    <span>Tüm Kataloğu Çek</span>
                </button>
            </form>

            <button onclick="document.getElementById('filteredSyncModal').classList.remove('hidden')" 
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
                <i class="fa-solid fa-sliders"></i>
                <span>Filtreli Paket Çek (Ülke & Tip)</span>
            </button>
        </div>
    </div>

    <!-- Section 1: Bulk / Single Customer Package Assignment Form -->
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-blue-600 space-y-5 bg-white border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-blue-600"></i>
                    <span>Toplu Müşteri Paket Atama & Fiyatlandırma</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Tüm müşterilerinize veya seçeceğiniz müşterilere topluca paket atayabilir, alış fiyatları üzerine kâr marjı ekleyebilirsiniz.</p>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-xl border border-slate-200 self-start sm:self-auto">
                <button type="button" onclick="setAssignMode('bulk')" id="btnModeBulk" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white shadow transition">
                    <i class="fa-solid fa-list-check"></i> Toplu Atama
                </button>
                <button type="button" onclick="setAssignMode('single')" id="btnModeSingle" class="px-3 py-1.5 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-900 transition">
                    <i class="fa-solid fa-user-tag"></i> Tekil Atama
                </button>
            </div>
        </div>

        <form action="{{ route('admin.packages.assign') }}" method="POST" id="assignForm" class="space-y-6">
            @csrf
            <input type="hidden" name="assign_mode" id="assignModeInput" value="bulk">

            <!-- Step 1: Target Customers Selection -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">1. Hedef Müşteri Seçimi</label>
                <div class="flex flex-wrap gap-4 items-center">
                    <label class="flex items-center gap-2 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_customers" value="all" checked onchange="toggleCustomerSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-slate-900">Tüm Müşteriler (Hepsine Atansın)</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold font-mono">{{ count($customers) }} Müşteri</span>
                    </label>

                    <label class="flex items-center gap-2 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_customers" value="selected" id="targetCustSelectedRadio" onchange="toggleCustomerSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-slate-900">Seçili Müşteriler</span>
                    </label>
                </div>

                <!-- Customer Checkboxes Container -->
                <div id="customerCheckboxesContainer" class="hidden pt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200 max-h-48 overflow-y-auto">
                    @foreach($customers as $c)
                        <label class="flex items-center gap-2 p-2 hover:bg-white rounded-lg cursor-pointer text-xs border border-transparent hover:border-slate-200">
                            <input type="checkbox" name="user_ids[]" value="{{ $c->id }}" {{ request()->query('customer_id') == $c->id ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="font-bold text-slate-800">{{ $c->name }}</span>
                            <span class="text-slate-500 text-[11px]">({{ $c->company_name ?? 'Bireysel' }})</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 2: Target Packages Selection -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">2. Hedef eSIM Paket Seçimi</label>
                <div class="flex flex-wrap gap-4 items-center">
                    <label class="flex items-center gap-2 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_products" value="all" checked onchange="toggleProductSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-slate-900">Tüm Paketler (Tüm Kataloğu At)</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-bold font-mono">{{ count($products) }} Paket</span>
                    </label>

                    <label class="flex items-center gap-2 bg-slate-50 px-4 py-2.5 rounded-xl border border-slate-200 cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="target_products" value="selected" id="targetProdSelectedRadio" onchange="toggleProductSelection(this.value)" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-bold text-slate-900">Seçili Paketler</span>
                    </label>
                </div>

                <!-- Product Checkboxes Container -->
                <div id="productCheckboxesContainer" class="hidden pt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200 max-h-56 overflow-y-auto">
                    @foreach($products as $p)
                        <label class="flex items-center justify-between p-2 hover:bg-white rounded-lg cursor-pointer text-xs border border-transparent hover:border-slate-200">
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span class="font-bold text-slate-800">{{ $p->product_name }}</span>
                            </div>
                            <span class="font-mono text-cyan-700 font-bold">₺{{ number_format($p->net_price, 2) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Step 3: Pricing Strategy -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">3. Satış Fiyatlandırma Stratejisi & Kâr Marjı</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs text-slate-500 font-bold mb-1">Fiyat Belirleme Türü</label>
                        <select name="pricing_type" id="pricingTypeSelect" onchange="updatePriceLabel()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
                            <option value="margin_percent">Alış Fiyatına % Kar Marjı Ekle (%)</option>
                            <option value="margin_fixed">Alış Fiyatına Sabit Tutarlı Kar Ekle (₺)</option>
                            <option value="fixed">Sabit Satış Fiyatı Belirle (₺)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-500 font-bold mb-1" id="priceValueLabel">Kâr Marjı Yüzdesi (%)</label>
                        <input type="number" step="0.01" min="0" name="price_value" id="priceValueInput" required value="30" placeholder="Örn: 30"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-black focus:outline-none focus:border-blue-600">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full py-2.5 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <span>Toplu Paket Atamasını Tamamla</span>
                        </button>
                    </div>
                </div>
                <p id="pricingHint" class="text-xs text-slate-500 mt-1 font-medium">Örnek: Alış fiyatı ₺100 olan pakete %30 kâr eklendiğinde müşteriye ₺130.00 fiyatla atanır.</p>
            </div>
        </form>
    </div>

    <!-- Section 2: Catalog of Available Products -->
    <div class="glass-panel p-6 rounded-2xl space-y-4 bg-white border border-slate-200 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-600"></i>
                    <span>Canlı Paketler Katalog Tablosu</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Sistemde kayıtlı paketler arasında anlık arama ve filtreleme yapın.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200" id="filterCountBadge">
                    Gösterilen: {{ count($products) }} / {{ count($products) }} Paket
                </span>
            </div>
        </div>

        <!-- Live Instant Filter Bar -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200">
            <!-- Filter 1: Search Box -->
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                <input type="text" id="tableSearchInput" onkeyup="filterCatalogTable()" placeholder="Paket Adı veya Kodu Ara..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
            </div>

            <!-- Filter 2: Country Filter -->
            <div>
                <select id="tableCountryFilter" onchange="filterCatalogTable()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                    <option value="">-- Tüm Ülkeler --</option>
                    <option value="tr">🇹🇷 Türkiye (TR)</option>
                    <option value="us">🇺🇸 Amerika (US)</option>
                    <option value="gb">🇬🇧 İngiltere (GB)</option>
                    <option value="de">🇩🇪 Almanya (DE)</option>
                    <option value="fr">🇫🇷 Fransa (FR)</option>
                    <option value="it">🇮🇹 İtalya (IT)</option>
                    <option value="es">🇪🇸 İspanya (ES)</option>
                    <option value="jp">🇯🇵 Japonya (JP)</option>
                    <option value="kr">🇰🇷 Güney Kore (KR)</option>
                    <option value="th">🇹🇭 Tayland (TH)</option>
                    <option value="ae">🇦🇪 BAE (AE)</option>
                </select>
            </div>

            <!-- Filter 3: Product Type Filter -->
            <div>
                <select id="tableTypeFilter" onchange="filterCatalogTable()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                    <option value="">-- Tüm Paket Tipleri --</option>
                    <option value="DATA_PACK">📦 Sabit Toplam Veri (DATA_PACK)</option>
                    <option value="DAILY_PACK">⚡ Günlük Yenilenen (DAILY_PACK)</option>
                </select>
            </div>

            <!-- Filter 4: Duration Filter -->
            <div>
                <select id="tablePeriodFilter" onchange="filterCatalogTable()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                    <option value="">-- Tüm Süreler --</option>
                    <option value="1">1 Gün</option>
                    <option value="3">3 Gün</option>
                    <option value="7">7 Gün</option>
                    <option value="8">8 Gün</option>
                    <option value="10">10 Gün</option>
                    <option value="15">15 Gün</option>
                    <option value="30">30 Gün</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700" id="catalogTable">
                <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Paket Kodu</th>
                        <th class="py-3.5 px-4 font-bold">Paket Adı</th>
                        <th class="py-3.5 px-4 font-bold">Ülkeler</th>
                        <th class="py-3.5 px-4 font-bold">Alış Fiyatı (Net USD)</th>
                        <th class="py-3.5 px-4 font-bold">Veri Miktarı</th>
                        <th class="py-3.5 px-4 font-bold">Kullanım Süresi</th>
                        <th class="py-3.5 px-4 font-bold text-center">Atandığı Müşteri</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="catalog-row hover:bg-slate-50 transition" 
                            data-code="{{ strtolower($product->product_code) }}"
                            data-name="{{ strtolower($product->product_name) }}"
                            data-countries="{{ strtolower(implode(',', $product->country_code_list ?? [])) }}"
                            data-type="{{ $product->product_type }}"
                            data-period="{{ $product->usage_period }}">
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-700 font-bold">{{ $product->product_code }}</td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $product->product_name }}</td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice($product->country_code_list ?? [], 0, 4) as $country)
                                        <span class="px-2 py-0.5 bg-slate-100 rounded text-xs font-mono text-slate-700 border border-slate-200 font-semibold">{{ $country }}</span>
                                    @endforeach
                                    @if(count($product->country_code_list ?? []) > 4)
                                        <span class="text-xs text-slate-500 font-medium align-middle">+{{ count($product->country_code_list) - 4 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">${{ number_format($product->net_price, 2) }} USD</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                    {{ $product->data_total }} {{ $product->data_unit }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">{{ $product->usage_period }} Gün</td>
                            <td class="py-3.5 px-4 text-center font-bold text-purple-700">
                                {{ $product->customer_packages_count }} Müşteri
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 font-medium">Henüz paket çekilmemiş. Yukarıdaki senkronizasyon butonunu kullanabilirsiniz.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Filtered Package Sync from TGT API -->
<div id="filteredSyncModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-lg w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-blue-600"></i>
                <span>Filtreli TGT Paket Çekme</span>
            </h3>
            <button onclick="document.getElementById('filteredSyncModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.packages.sync') }}" method="POST" class="mt-4 space-y-4">
            @csrf

            <!-- Country / Region Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-earth-americas text-blue-600"></i>
                    <span>Ülke / Bölge Seçimi</span>
                </label>
                <select name="country_code" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
                    <option value="">-- Tüm Ülkeler & Bölgeler --</option>
                    <option value="TR">🇹🇷 Türkiye (TR)</option>
                    <option value="US">🇺🇸 Amerika Birleşik Devletleri (US)</option>
                    <option value="GB">🇬🇧 Birleşik Krallık / İngiltere (GB)</option>
                    <option value="DE">🇩🇪 Almanya (DE)</option>
                    <option value="FR">🇫🇷 Fransa (FR)</option>
                    <option value="IT">🇮🇹 İtalya (IT)</option>
                    <option value="ES">🇪🇸 İspanya (ES)</option>
                    <option value="JP">🇯🇵 Japonya (JP)</option>
                    <option value="KR">🇰🇷 Güney Kore (KR)</option>
                    <option value="TH">🇹🇭 Tayland (TH)</option>
                    <option value="AE">🇦🇪 Birleşik Arap Emirlikleri (AE)</option>
                    <option value="IL">🇮🇱 İsrail (IL)</option>
                </select>
                <span class="text-[11px] text-slate-400 mt-1 block font-medium">Belli bir ülkeye özel paketleri çekmek için ülke seçin.</span>
            </div>

            <!-- Product Type Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-box text-indigo-600"></i>
                    <span>Paket Tipi</span>
                </label>
                <select name="product_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
                    <option value="">-- Tüm Paket Tipleri --</option>
                    <option value="DATA_PACK">📦 Sabit Toplam Veri Paketi (DATA_PACK - Örn: 3GB, 5GB, 10GB)</option>
                    <option value="DAILY_PACK">⚡ Günlük Yenilenen Paket (DAILY_PACK - Örn: Günlük 1GB/2GB)</option>
                </select>
            </div>

            <!-- Usage Period Filter -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1 flex items-center gap-1.5">
                    <i class="fa-solid fa-clock text-amber-500"></i>
                    <span>Kullanım Süresi (Gün)</span>
                </label>
                <select name="usage_period" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
                    <option value="">-- Tüm Süreler --</option>
                    <option value="1">1 Günlük</option>
                    <option value="3">3 Günlük</option>
                    <option value="7">7 Günlük</option>
                    <option value="8">8 Günlük</option>
                    <option value="10">10 Günlük</option>
                    <option value="15">15 Günlük</option>
                    <option value="30">30 Günlük</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('filteredSyncModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-xl font-bold">İptal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>Seçili Paketleri API'den Çek</span>
                </button>
            </div>
        </form>
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
            btnBulk.className = 'px-3 py-1.5 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-900 transition';
            radCustSelected.checked = true;
            radProdSelected.checked = true;
            toggleCustomerSelection('selected');
            toggleProductSelection('selected');
        } else {
            btnBulk.className = 'px-3 py-1.5 text-xs font-bold rounded-lg bg-blue-600 text-white shadow transition';
            btnSingle.className = 'px-3 py-1.5 text-xs font-bold rounded-lg text-slate-600 hover:text-slate-900 transition';
        }
    }

    function filterCatalogTable() {
        const search = (document.getElementById('tableSearchInput').value || '').toLowerCase().trim();
        const country = (document.getElementById('tableCountryFilter').value || '').toLowerCase().trim();
        const type = (document.getElementById('tableTypeFilter').value || '').trim();
        const period = (document.getElementById('tablePeriodFilter').value || '').trim();

        const rows = document.querySelectorAll('.catalog-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const code = row.getAttribute('data-code') || '';
            const name = row.getAttribute('data-name') || '';
            const countries = row.getAttribute('data-countries') || '';
            const rowType = row.getAttribute('data-type') || '';
            const rowPeriod = row.getAttribute('data-period') || '';

            let matchesSearch = !search || code.includes(search) || name.includes(search) || countries.includes(search);
            let matchesCountry = !country || countries.includes(country);
            let matchesType = !type || rowType === type;
            let matchesPeriod = !period || rowPeriod === period;

            if (matchesSearch && matchesCountry && matchesType && matchesPeriod) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const badge = document.getElementById('filterCountBadge');
        if (badge) {
            badge.innerText = `Gösterilen: ${visibleCount} / ${rows.length} Paket`;
        }
    }
</script>
@endsection
