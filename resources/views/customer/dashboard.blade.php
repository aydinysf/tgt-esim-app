@extends('layouts.app')

@section('title', 'eSIM Satış & Paket Mağazası — POLO SIM')

@section('content')
<div class="space-y-6">
    <!-- Top Greeting Header & Balance Overview -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-cart-shopping text-blue-600"></i>
                <span>eSIM Satış Ekranı</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                @if(Auth::user()->isBranchUser())
                    <span><strong>{{ Auth::user()->branch->name ?? 'Şubeniz' }}</strong> adına paket seçip müşterinize anında eSIM satışı ve aktivasyonu yapabilirsiniz.</span>
                @else
                    <span>Bayinize tanımlı aktif paketleri listeleyin, şubeleriniz adına anında eSIM satışı gerçekleştirin.</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(!Auth::user()->isBranchUser())
                <!-- Balance Card (Only visible to Boss / Main Dealer Account) -->
                <div class="glass-panel px-5 py-3 rounded-2xl border-l-4 border-cyan-500 flex items-center gap-3 shadow-md bg-white">
                    <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg font-bold shrink-0 border border-cyan-200">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Hesap Bakiyesi</span>
                        <div class="text-xl font-black text-cyan-700 leading-tight">€{{ number_format($effectiveCustomer->balance, 2) }}</div>
                    </div>
                </div>
            @endif

            <!-- Branch / Company Badge -->
            <div class="glass-card px-4 py-3 rounded-2xl text-xs text-slate-700 border border-slate-200 items-center gap-2.5 shadow-sm bg-white flex">
                <div class="w-8 h-8 rounded-xl {{ Auth::user()->isBranchUser() ? 'bg-indigo-50 text-indigo-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center font-bold shrink-0 border border-slate-200">
                    <i class="fa-solid {{ Auth::user()->isBranchUser() ? 'fa-store' : 'fa-building' }}"></i>
                </div>
                <div class="truncate">
                    <span class="font-extrabold block text-slate-900 leading-tight">{{ Auth::user()->name }}</span>
                    <span class="text-[10px] font-bold {{ Auth::user()->isBranchUser() ? 'text-indigo-600' : 'text-slate-400' }} block">
                        {{ Auth::user()->isBranchUser() ? (Auth::user()->branch->name ?? 'Şube Personeli') : (Auth::user()->company_name ?? 'Bayi Ana Hesabı') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Category Tabs (eSIM vs IWC) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-3">
        <div class="flex items-center gap-2">
            <button type="button" onclick="switchProductTab('iwc')" id="tabBtnIwc"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs transition flex items-center gap-2 shadow-sm {{ $activeTab === 'iwc' ? 'bg-amber-500 text-white shadow-amber-200' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <i class="fa-solid fa-landmark text-xs"></i>
                <span>IWC Tur & Deneyim Paketleri (Kredi Kartı ile)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'iwc' ? 'bg-amber-700 text-white' : 'bg-amber-100 text-amber-800' }}">
                    {{ count($iwcProducts) }}
                </span>
            </button>

            <button type="button" onclick="switchProductTab('esim')" id="tabBtnEsim"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs transition flex items-center gap-2 shadow-sm {{ $activeTab === 'esim' ? 'bg-blue-600 text-white shadow-blue-200' : 'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50' }}">
                <i class="fa-solid fa-cart-shopping text-xs"></i>
                <span>eSIM Veri Paketleri (Bakiye ile)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $activeTab === 'esim' ? 'bg-blue-800 text-white' : 'bg-slate-100 text-slate-600' }}">
                    {{ count($assignedPackages) }}
                </span>
            </button>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('customer.iwc.orders') }}" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs rounded-xl border border-amber-200 transition inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-ticket text-amber-600 text-xs"></i>
                <span>Satın Alınan Turlar ({{ $iwcOrdersCount }})</span>
            </a>
        </div>
    </div>

    <!-- ==================== IWC PACKAGES TAB CONTENT ==================== -->
    <div id="iwcTabContent" class="{{ $activeTab === 'iwc' ? '' : 'hidden' }} space-y-6">
        <!-- IWC Search & Filter Toolbar -->
        <div class="glass-card p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-96">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="iwcSearchInput" onkeyup="filterIwcPackages()" placeholder="IWC tur veya deneyim adı ara..." 
                    class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white transition">
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-slate-500">
                    Ödeme Modeli: <span class="text-emerald-700 font-extrabold"><i class="fa-brands fa-stripe text-indigo-600 mr-1 text-sm"></i>Kredi Kartı / 3D Secure</span>
                </span>
            </div>
        </div>

        <!-- IWC Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="iwcGrid">
            @forelse($iwcProducts as $iwc)
                @php
                    $iVariants = $iwc->variants_data ?? [];
                @endphp
                <div class="iwc-card glass-card rounded-2xl p-6 flex flex-col justify-between space-y-5 hover:border-amber-400 hover:shadow-md transition duration-200 relative group bg-white border border-slate-200"
                     data-search="{{ strtolower($iwc->name . ' ' . $iwc->type . ' ' . strip_tags($iwc->description)) }}">
                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-landmark text-[10px]"></i>
                                <span>{{ ucfirst($iwc->type ?: 'Deneyim') }}</span>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1">
                                <i class="fa-solid fa-credit-card text-[9px]"></i>
                                <span>Kredi Kartı</span>
                            </span>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900 leading-snug group-hover:text-amber-600 transition">
                            {{ $iwc->name }}
                        </h3>

                        <div class="text-xs text-slate-500 line-clamp-3 leading-relaxed">
                            {!! strip_tags($iwc->description ?: 'Detaylı rezervasyon ve tarih seçenekleri.') !!}
                        </div>

                        @if(!empty($iVariants))
                            <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
                                @foreach(array_slice($iVariants, 0, 2) as $iv)
                                    @php
                                        $ivLabel = is_array($iv['label'] ?? null) ? ($iv['label']['tr'] ?? $iv['label']['en'] ?? reset($iv['label'])) : ($iv['label'] ?? 'Standart');
                                    @endphp
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-semibold text-slate-700 border border-slate-200 truncate max-w-[200px]">
                                        <i class="fa-solid fa-check text-[9px] text-emerald-600 mr-1"></i>{{ $ivLabel }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Başlangıç Fiyatı</span>
                            <div class="text-xl font-black text-slate-900 leading-tight">
                                €{{ number_format($iwc->price, 2) }}
                            </div>
                        </div>

                        <a href="{{ route('customer.iwc.show', $iwc->id) }}"
                           class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-extrabold text-xs rounded-xl transition flex items-center gap-2 shadow-md active:scale-95">
                            <i class="fa-solid fa-credit-card text-xs"></i>
                            <span>Kredi Kartı ile Satın Al</span>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center glass-card rounded-2xl bg-white border border-slate-200">
                    <p class="text-xs text-slate-500">Kayıtlı IWC paketi bulunamadı.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ==================== ESIM PACKAGES TAB CONTENT ==================== -->
    <div id="esimTabContent" class="{{ $activeTab === 'esim' ? '' : 'hidden' }} space-y-6">
    <!-- Quick Navigation & Search / Filter Toolbar -->
    <div class="glass-card p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-96">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" id="packageSearchInput" onkeyup="filterPackages()" placeholder="Ülke veya paket adı ara (Örn: Turkey, Spain, Europe)..." 
                class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition">
        </div>

        <!-- Direct Link to Sold Packages Page -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <div class="text-xs text-slate-500 font-semibold">
                Tanımlı Paket: <span class="font-extrabold text-blue-700">{{ count($assignedPackages) }}</span>
            </div>

            <a href="{{ route('customer.orders.index') }}" class="px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-xl border border-purple-200 transition inline-flex items-center gap-2 shadow-sm active:scale-95">
                <i class="fa-solid fa-box-archive text-sm"></i>
                <span>Satılan Paketler & Geçmiş ({{ $totalOrdersCount }}) ➔</span>
            </a>
        </div>
    </div>

    <!-- Packages Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="packagesGrid">
        @forelse($assignedPackages as $assignment)
            @php 
                $product = $assignment->product; 
                $countryList = $product->country_code_list ?? [];
                $searchKeywords = strtolower($product->display_name . ' ' . $product->product_code . ' ' . implode(' ', $countryList));
            @endphp
            <div class="package-card glass-card rounded-2xl p-6 flex flex-col justify-between space-y-5 hover:border-blue-400 transition shadow-sm relative group bg-white border border-slate-200"
                 data-search="{{ $searchKeywords }}">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-globe text-[10px]"></i>
                            <span>Veri Paketi</span>
                        </span>
                        <span class="text-xs font-mono text-slate-400 font-semibold">{{ $product->card_type ?? 'eSIM' }}</span>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 leading-snug group-hover:text-blue-600 transition">
                        {{ $product->display_name }}
                    </h3>

                    <!-- Supported Countries -->
                    <div class="flex flex-wrap gap-1">
                        @foreach(array_slice($countryList, 0, 5) as $code)
                            <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-mono text-slate-700 border border-slate-200 font-semibold">{{ $code }}</span>
                        @endforeach
                        @if(count($countryList) > 5)
                            <span class="text-[11px] text-slate-400 font-medium align-middle self-center">+{{ count($countryList) - 5 }} ülke</span>
                        @endif
                    </div>

                    <!-- Quota Metric -->
                    <div class="pt-2">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex items-center justify-between px-4">
                            <span class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                                <i class="fa-solid fa-wifi text-blue-600"></i>
                                <span>Veri Kotası</span>
                            </span>
                            <span class="text-base font-black text-blue-700">{{ $product->data_total }} {{ $product->data_unit }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Bar (Price hidden from Staff) -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    @if(!Auth::user()->isBranchUser())
                        <div>
                            <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Satış Fiyatı</div>
                            <div class="text-2xl font-black text-slate-900">€{{ number_format($assignment->sale_price, 2) }}</div>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5 text-xs text-emerald-700 font-bold bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                            <i class="fa-solid fa-circle-check text-[11px]"></i>
                            <span>Kullanıma Hazır</span>
                        </div>
                    @endif

                    <button type="button" 
                        data-package-id="{{ $assignment->id }}"
                        data-name="{{ $product->display_name }}"
                        data-price="{{ number_format($assignment->sale_price, 2) }}"
                        data-data="{{ $product->data_total }} {{ $product->data_unit }}"
                        onclick="openPaymentModalFromBtn(this)"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>Müşteriye Sat</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-panel p-16 text-center text-slate-500 space-y-4 bg-white rounded-3xl border border-slate-200">
                <i class="fa-solid fa-box-open text-5xl text-slate-300"></i>
                <div class="space-y-1">
                    <p class="text-base font-bold text-slate-800">Henüz size tanımlanmış özel bir eSIM paketi bulunmuyor.</p>
                    <p class="text-xs text-slate-400">Yöneticiniz bayiniz için paket tanımladığında bu alanda anında görünecektir.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal: Account Balance Checkout Modal -->
<div id="checkoutModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 overflow-y-auto">
    <div class="bg-white max-w-md w-full p-6 md:p-7 rounded-3xl shadow-2xl space-y-5 relative border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-emerald-600"></i>
                <span>eSIM Satış Onayı</span>
            </h3>
            <button onclick="document.getElementById('checkoutModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl font-bold p-1">&times;</button>
        </div>

        @if(!Auth::user()->isBranchUser())
            <!-- Account Balance Summary (For Dealer Boss Only) -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5 text-xs">
                <div class="flex items-center justify-between text-slate-500 font-medium">
                    <span>Mevcut Hesap Bakiyesi:</span>
                    <span class="font-black text-sm text-cyan-700">€{{ number_format($effectiveCustomer->balance, 2) }}</span>
                </div>
                
                <div class="flex items-center justify-between text-slate-600 border-t border-slate-200/80 pt-2">
                    <span>Seçilen Paket:</span>
                    <span class="font-bold text-slate-900 text-right truncate max-w-[200px]" id="modalPackName">Paket Adı</span>
                </div>

                <div class="flex items-center justify-between text-slate-600">
                    <span>Paket Satış Bedeli:</span>
                    <span class="font-black text-rose-600 text-sm">-€<span id="modalPackPrice">0.00</span></span>
                </div>

                <div class="flex items-center justify-between text-slate-800 border-t border-slate-200/80 pt-2 font-bold">
                    <span>İşlem Sonrası Kalan Bakiye:</span>
                    <span class="font-black text-emerald-600 text-sm">€<span id="modalRemainingPrice">0.00</span></span>
                </div>
            </div>
        @else
            <!-- Clean Summary for Staff (No Price / No Balance) -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-500 font-medium">Satılacak Paket:</span>
                    <span class="font-bold text-slate-900 text-right truncate max-w-[220px]" id="modalPackName">Paket Adı</span>
                </div>
                <div class="flex items-center justify-between text-slate-600 border-t border-slate-200/80 pt-2">
                    <span class="text-slate-500 font-medium">İşlem Yapan Şube:</span>
                    <span class="font-bold text-indigo-700">{{ Auth::user()->branch->name ?? 'Şubeniz' }}</span>
                </div>
            </div>
        @endif

        <!-- Purchase Confirmation Form -->
        <form action="{{ route('customer.buy') }}" method="POST" id="checkoutForm" class="space-y-4">
            @csrf
            <input type="hidden" name="customer_package_id" id="modalCustomerPackageId">

            @if(!Auth::user()->isBranchUser() && count($branches) > 0)
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Satışı Yapan Şube</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600 text-xs">
                        <option value="">-- Merkez / Genel --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2">
                <i class="fa-solid fa-circle-info text-blue-600 text-sm shrink-0 mt-0.5"></i>
                <span>Satışı onayladığınızda müşterinize ait eSIM QR kodu ve kurulum bilgileri anında oluşturulup ekrana açılacaktır.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('checkoutModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs rounded-xl font-bold">İptal</button>
                <button type="submit" id="paySubmitBtn"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2 text-xs active:scale-95">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Satışı Onayla ve QR Üret</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const IS_STAFF = {{ Auth::user()->isBranchUser() ? 'true' : 'false' }};

    function switchProductTab(tab) {
        const iwcContent = document.getElementById('iwcTabContent');
        const esimContent = document.getElementById('esimTabContent');
        const iwcBtn = document.getElementById('tabBtnIwc');
        const esimBtn = document.getElementById('tabBtnEsim');

        if (tab === 'iwc') {
            iwcContent.classList.remove('hidden');
            esimContent.classList.add('hidden');
            iwcBtn.className = 'px-4 py-2.5 rounded-xl font-extrabold text-xs transition flex items-center gap-2 shadow-sm bg-amber-500 text-white shadow-amber-200';
            esimBtn.className = 'px-4 py-2.5 rounded-xl font-extrabold text-xs transition flex items-center gap-2 shadow-sm bg-white text-slate-700 border border-slate-200 hover:bg-slate-50';
        } else {
            iwcContent.classList.add('hidden');
            esimContent.classList.remove('hidden');
            esimBtn.className = 'px-4 py-2.5 rounded-xl font-extrabold text-xs transition flex items-center gap-2 shadow-sm bg-blue-600 text-white shadow-blue-200';
            iwcBtn.className = 'px-4 py-2.5 rounded-xl font-extrabold text-xs transition flex items-center gap-2 shadow-sm bg-white text-slate-700 border border-slate-200 hover:bg-slate-50';
        }
    }

    function filterIwcPackages() {
        const query = (document.getElementById('iwcSearchInput').value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.iwc-card');
        cards.forEach(card => {
            const keywords = card.getAttribute('data-search') || '';
            if (!query || keywords.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function filterPackages() {
        const query = (document.getElementById('packageSearchInput').value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.package-card');
        cards.forEach(card => {
            const keywords = card.getAttribute('data-search') || '';
            if (!query || keywords.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function openPaymentModalFromBtn(btn) {
        const packageId = btn.getAttribute('data-package-id');
        const packName = btn.getAttribute('data-name');
        const price = btn.getAttribute('data-price') || '0';
        const userBalance = {{ (float) $effectiveCustomer->balance }};
        
        document.getElementById('modalCustomerPackageId').value = packageId;
        const nameEl = document.getElementById('modalPackName');
        if (nameEl) nameEl.innerText = packName;

        if (!IS_STAFF) {
            const priceEl = document.getElementById('modalPackPrice');
            if (priceEl) priceEl.innerText = parseFloat(price).toFixed(2);

            const remaining = userBalance - parseFloat(price);
            const remSpan = document.getElementById('modalRemainingPrice');
            if (remSpan) {
                remSpan.innerText = remaining.toFixed(2);
                if (remaining < 0) {
                    remSpan.className = 'font-black text-rose-600 text-sm';
                } else {
                    remSpan.className = 'font-black text-emerald-600 text-sm';
                }
            }
        }

        const modal = document.getElementById('checkoutModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
</script>
@endpush
@endsection
