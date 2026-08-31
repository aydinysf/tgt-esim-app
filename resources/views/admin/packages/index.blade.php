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

                <!-- Product Checkboxes Wrapper - loaded via AJAX on demand -->
                <div id="productCheckboxesWrapper" class="hidden space-y-3 pt-3">
                    <input type="text" id="assignProductSearch" onkeyup="filterAssignProducts()" placeholder="🔍 Paket adı ile ara..."
                        class="w-full px-3 py-2 text-sm bg-white border border-slate-300 rounded-lg shadow-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">

                    <div id="assignProductList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 p-3 bg-slate-50 rounded-xl border border-slate-200 max-h-56 overflow-y-auto">
                        <!-- Products loaded via AJAX -->
                        <div id="assignProductLoader" class="col-span-3 py-6 flex items-center justify-center gap-3 text-slate-400 text-sm font-medium">
                            <i class="fa-solid fa-spinner fa-spin text-blue-500"></i> Paketler yükleniyor...
                        </div>
                    </div>
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
                            <option value="margin_fixed">Alış Fiyatına Sabit Tutarlı Kar Ekle (€)</option>
                            <option value="fixed">Sabit Satış Fiyatı Belirle (€)</option>
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
                <p id="pricingHint" class="text-xs text-slate-500 mt-1 font-medium">Örnek: Alış fiyatı €100 olan pakete %30 kâr eklendiğinde müşteriye €130.00 fiyatla atanır.</p>
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
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    Toplam: {{ $products->total() }} Paket &nbsp;|&nbsp; Sayfa {{ $products->currentPage() }} / {{ $products->lastPage() }}
                </span>
            </div>
        </div>

        <!-- Server-Side Filter + Per-Page Form -->
        <form method="GET" action="{{ route('admin.packages.index') }}" data-no-loader id="catalogFilterForm">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 p-3 bg-slate-50 rounded-2xl border border-slate-200">
                <!-- Search -->
                <div class="relative md:col-span-2">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Paket Adı veya Kodu Ara..."
                        class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                </div>

                <!-- Country -->
                <div>
                    <select name="country" onchange="this.form.submit()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                        <option value="">-- Tüm Ülkeler --</option>
                        <option value="TR" {{ $country === 'TR' ? 'selected' : '' }}>🇹🇷 Türkiye (TR)</option>
                        <option value="US" {{ $country === 'US' ? 'selected' : '' }}>🇺🇸 Amerika (US)</option>
                        <option value="GB" {{ $country === 'GB' ? 'selected' : '' }}>🇬🇧 İngiltere (GB)</option>
                        <option value="DE" {{ $country === 'DE' ? 'selected' : '' }}>🇩🇪 Almanya (DE)</option>
                        <option value="FR" {{ $country === 'FR' ? 'selected' : '' }}>🇫🇷 Fransa (FR)</option>
                        <option value="IT" {{ $country === 'IT' ? 'selected' : '' }}>🇮🇹 İtalya (IT)</option>
                        <option value="ES" {{ $country === 'ES' ? 'selected' : '' }}>🇪🇸 İspanya (ES)</option>
                        <option value="JP" {{ $country === 'JP' ? 'selected' : '' }}>🇯🇵 Japonya (JP)</option>
                        <option value="KR" {{ $country === 'KR' ? 'selected' : '' }}>🇰🇷 Güney Kore (KR)</option>
                        <option value="TH" {{ $country === 'TH' ? 'selected' : '' }}>🇹🇭 Tayland (TH)</option>
                        <option value="AE" {{ $country === 'AE' ? 'selected' : '' }}>🇦🇪 BAE (AE)</option>
                    </select>
                </div>

                <!-- Type -->
                <div>
                    <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                        <option value="">-- Tüm Tipler --</option>
                        <option value="DATA_PACK"  {{ $type === 'DATA_PACK'  ? 'selected' : '' }}>📦 Sabit Veri</option>
                        <option value="DAILY_PACK" {{ $type === 'DAILY_PACK' ? 'selected' : '' }}>⚡ Günlük</option>
                    </select>
                </div>

                <!-- Search button + Clear -->
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow transition flex items-center justify-center gap-1">
                        <i class="fa-solid fa-search"></i> Ara
                    </button>
                    @if($search || $country || $type || $period)
                    <a href="{{ route('admin.packages.index', ['per_page' => $perPage]) }}" class="py-2 px-3 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl shadow transition flex items-center justify-center" title="Filtreyi Temizle">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Per-page selector row -->
        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-500 font-medium">
                {{ $products->firstItem() }}–{{ $products->lastItem() }} arası gösteriliyor (Toplam {{ $products->total() }})
            </p>
            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Sayfa başına:</label>
                <select onchange="window.location='{{ route('admin.packages.index') }}?' + new URLSearchParams({...Object.fromEntries(new URLSearchParams(location.search)), per_page: this.value, page: 1}).toString()"
                    class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
                    @foreach([10, 25, 50, 100, 250] as $n)
                        <option value="{{ $n }}" {{ $perPage == $n ? 'selected' : '' }}>{{ $n }} kayıt</option>
                    @endforeach
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
                        <th class="py-3.5 px-4 font-bold text-center">İşlemler</th>
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
                                    @foreach(array_slice($product->country_code_list ?? [], 0, 4) as $c)
                                        <span class="px-2 py-0.5 bg-slate-100 rounded text-xs font-mono text-slate-700 border border-slate-200 font-semibold">{{ $c }}</span>
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
                            <td class="py-3.5 px-4 text-center">
                                <form action="{{ route('admin.packages.destroy', $product->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Bu paketi silmek istediğinize emin misiniz? (Müşterilerden de silinir)');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:text-rose-700 rounded-lg text-xs font-bold transition">
                                        <i class="fa-solid fa-trash"></i> Sil
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 font-medium">Henüz paket çekilmemiş. Yukarıdaki senkronizasyon butonunu kullanabilirsiniz.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-slate-100">
            <p class="text-xs text-slate-500 font-medium">
                Toplam <span class="font-bold text-slate-800">{{ $products->total() }}</span> paketten
                <span class="font-bold text-slate-800">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span> gösteriliyor
            </p>
            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if($products->onFirstPage())
                    <span class="px-3 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-200 rounded-lg cursor-not-allowed">‹</span>
                @else
                    <a href="{{ $products->previousPageUrl() }}" class="px-3 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-blue-400 transition">‹</a>
                @endif

                {{-- Page numbers --}}
                @php
                    $start = max(1, $products->currentPage() - 2);
                    $end   = min($products->lastPage(), $products->currentPage() + 2);
                @endphp

                @if($start > 1)
                    <a href="{{ $products->url(1) }}" class="px-3 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">1</a>
                    @if($start > 2) <span class="px-2 py-2 text-xs text-slate-400">…</span> @endif
                @endif

                @for($i = $start; $i <= $end; $i++)
                    @if($i == $products->currentPage())
                        <span class="px-3 py-2 text-xs font-bold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-sm">{{ $i }}</span>
                    @else
                        <a href="{{ $products->url($i) }}" class="px-3 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-blue-400 transition">{{ $i }}</a>
                    @endif
                @endfor

                @if($end < $products->lastPage())
                    @if($end < $products->lastPage() - 1) <span class="px-2 py-2 text-xs text-slate-400">…</span> @endif
                    <a href="{{ $products->url($products->lastPage()) }}" class="px-3 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">{{ $products->lastPage() }}</a>
                @endif

                {{-- Next --}}
                @if($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="px-3 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-blue-400 transition">›</a>
                @else
                    <span class="px-3 py-2 text-xs font-bold text-slate-300 bg-slate-50 border border-slate-200 rounded-lg cursor-not-allowed">›</span>
                @endif
            </div>
        </div>
        @endif

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

        <form action="{{ route('admin.packages.sync-chunked') }}" method="POST" id="syncForm" data-no-loader onsubmit="startChunkedSync(event)">
            @csrf

            <!-- Sync Progress Indicator (Hidden by default) -->
            <div id="syncProgressContainer" class="hidden mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-3">
                <div class="flex justify-between text-xs font-bold text-blue-800">
                    <span id="syncProgressText">Senkronizasyon başlatılıyor...</span>
                    <span id="syncProgressPercentage">0%</span>
                </div>
                <div class="w-full bg-blue-200 rounded-full h-2.5 overflow-hidden">
                    <div id="syncProgressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p class="text-[10px] text-blue-600 font-mono" id="syncProgressDetails">Lütfen bekleyin, sayfadan ayrılmayın.</p>
            </div>

            <div id="syncFormInputs" class="space-y-4">
                <!-- Product Name Search -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass text-blue-600"></i>
                        <span>Paket Adında Kelime Ara (Örn: Turkey, Europe, Japan)</span>
                    </label>
                    <input type="text" name="product_name" placeholder="Örn: Turkey" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
                    <span class="text-[11px] text-slate-400 mt-1 block font-medium">Belli bir isim geçen paketleri (Örn: Turkey) çekmek için yazın.</span>
                </div>

                <!-- Card Type / Family Filter -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card text-purple-600"></i>
                        <span>Kart Tipi / Ailesi (Card Type)</span>
                    </label>
                    <select name="card_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
                        <option value="">-- Tüm Kart Tipleri --</option>
                        <option value="ep2">🇹🇷 ep2 (Turkey Daypass / High-Speed 82+ Türkiye Paketi Burada!)</option>
                        <option value="ep1">🇪🇺 ep1 (Euro / Global eP1 Paketleri)</option>
                        <option value="eO1">⚡ eO1 (Euro-eO1 Yüksek Hızlı İnternet Paketleri)</option>
                        <option value="M1">🕌 M1 (Middle East / Israel M1 Paketleri)</option>
                        <option value="A1">🌏 A1 (AIS Thailand & Asya Paketleri)</option>
                        <option value="C4">🌸 C4 (Asya C4 Günlük Paketler)</option>
                        <option value="F2">🇬🇧 F2 (İngiltere / Avrupa F2 Paketleri)</option>
                    </select>
                </div>

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
                    </select>
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

                <!-- Sync Depth / Pages -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-layer-group text-emerald-600"></i>
                        <span>Çekim Derinliği (Sayfa / Paket Adedi)</span>
                    </label>
                    <select name="max_pages" id="syncMaxPages" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
                        <option value="120" selected>🚀 TÜM PAKETLERİ EKSİKSİZ ÇEK (Tüm Sayfaları Sonu Gelene Kadar Tara)</option>
                        <option value="50">50 Sayfa Derinlik (Yaklaşık 5.000 Paket)</option>
                        <option value="10">10 Sayfa Derinlik (Yaklaşık 1.000 Paket)</option>
                        <option value="5">5 Sayfa Derinlik (Yaklaşık 500 Paket)</option>
                        <option value="1">Yalnızca 1. Sayfa (100 Paket)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100" id="syncFormButtons">
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

    let productsLoaded = false;

    function toggleProductSelection(val) {
        const wrapper = document.getElementById('productCheckboxesWrapper');
        if (val === 'selected') {
            wrapper.classList.remove('hidden');
            // Lazy load products via AJAX only once
            if (!productsLoaded) {
                loadAssignProducts();
            }
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function loadAssignProducts() {
        fetch('{{ route('admin.packages.products-json') }}')
            .then(r => r.json())
            .then(products => {
                productsLoaded = true;
                const list = document.getElementById('assignProductList');
                list.innerHTML = products.map(p => `
                    <label class="assign-product-label flex items-center justify-between p-2 hover:bg-white rounded-lg cursor-pointer text-xs border border-transparent hover:border-slate-200" data-name="${p.product_name.toLowerCase()}">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="product_ids[]" value="${p.id}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="font-bold text-slate-800">${p.product_name}</span>
                        </div>
                        <span class="font-mono text-emerald-700 font-bold">$${parseFloat(p.net_price).toFixed(2)} USD</span>
                    </label>
                `).join('');
            })
            .catch(() => {
                document.getElementById('assignProductList').innerHTML =
                    '<div class="col-span-3 text-center text-rose-500 py-4 text-sm">Paketler yüklenemedi, sayfayı yenileyin.</div>';
            });
    }

    function filterAssignProducts() {
        const search = document.getElementById('assignProductSearch').value.toLowerCase().trim();
        const labels = document.querySelectorAll('.assign-product-label');
        labels.forEach(label => {
            const name = label.getAttribute('data-name') || '';
            if (!search || name.includes(search)) {
                label.style.display = 'flex';
            } else {
                label.style.display = 'none';
            }
        });
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
            hint.innerText = 'Örnek: Alış fiyatı €100 olan pakete %30 kâr eklendiğinde müşteriye €130.00 satış fiyatıyla atanır.';
        } else if (type === 'margin_fixed') {
            label.innerText = 'Sabit Kâr Miktarı (€)';
            input.placeholder = 'Örn: 20';
            if (input.value == '30') input.value = '20';
            hint.innerText = 'Örnek: Alış fiyatı €100 olan pakete €20 kâr eklendiğinde müşteriye €120.00 satış fiyatıyla atanır.';
        } else {
            label.innerText = 'Sabit Satış Fiyatı (€)';
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

    async function startChunkedSync(event) {
        event.preventDefault();
        
        const form = event.target;
        const inputs = document.getElementById('syncFormInputs');
        const buttons = document.getElementById('syncFormButtons');
        const progressContainer = document.getElementById('syncProgressContainer');
        const progressBar = document.getElementById('syncProgressBar');
        const progressText = document.getElementById('syncProgressText');
        const progressDetails = document.getElementById('syncProgressDetails');
        const progressPercentage = document.getElementById('syncProgressPercentage');
        
        const maxPages = parseInt(document.getElementById('syncMaxPages').value);
        let currentPage = 1;
        let totalSaved = 0;

        // Hide inputs and show progress
        inputs.classList.add('hidden');
        buttons.classList.add('hidden');
        progressContainer.classList.remove('hidden');

        while (true) {
            progressText.innerText = `Sayfa ${currentPage} / ${maxPages} çekiliyor...`;
            let percent = Math.min(100, Math.round((currentPage / maxPages) * 100));
            progressBar.style.width = percent + '%';
            progressPercentage.innerText = percent + '%';
            
            try {
                const formData = new FormData(form);
                formData.append('page', currentPage);
                
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    totalSaved += result.saved_count || 0;
                    progressDetails.innerText = `Şu ana kadar ${totalSaved} eşleşen paket veritabanına kaydedildi.`;
                    
                    if (result.has_more && currentPage < maxPages) {
                        currentPage = result.next_page;
                    } else {
                        // Finished
                        progressText.innerText = 'Senkronizasyon tamamlandı!';
                        progressBar.style.width = '100%';
                        progressPercentage.innerText = '100%';
                        progressDetails.innerText = `Toplam ${totalSaved} paket kaydedildi. Sayfa yenileniyor...`;
                        
                        setTimeout(() => window.location.reload(), 1500);
                        break;
                    }
                } else {
                    // Error
                    progressText.innerText = 'Hata oluştu!';
                    progressText.classList.replace('text-blue-800', 'text-rose-800');
                    progressBar.classList.replace('bg-blue-600', 'bg-rose-600');
                    progressDetails.innerText = result.message || 'Bilinmeyen bir hata oluştu.';
                    buttons.classList.remove('hidden');
                    break;
                }
            } catch (err) {
                progressText.innerText = 'Bağlantı hatası!';
                progressDetails.innerText = err.message || 'İstek zaman aşımına uğradı veya koptu.';
                buttons.classList.remove('hidden');
                break;
            }
        }
    }
</script>
@endsection
