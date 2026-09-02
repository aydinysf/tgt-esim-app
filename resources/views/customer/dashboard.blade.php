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
            <!-- Balance Card -->
            <div class="glass-panel px-5 py-3 rounded-2xl border-l-4 border-cyan-500 flex items-center gap-3 shadow-md bg-white">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg font-bold shrink-0 border border-cyan-200">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Hesap Bakiyesi</span>
                    <div class="text-xl font-black text-cyan-700 leading-tight">€{{ number_format($effectiveCustomer->balance, 2) }}</div>
                </div>
            </div>

            <!-- Branch / Company Badge -->
            <div class="hidden sm:flex glass-card px-4 py-3 rounded-2xl text-xs text-slate-700 border border-slate-200 items-center gap-2 shadow-sm bg-white">
                <i class="fa-solid {{ Auth::user()->isBranchUser() ? 'fa-store text-indigo-600' : 'fa-building text-blue-600' }}"></i>
                <div class="truncate">
                    <span class="font-bold block text-slate-900">{{ Auth::user()->name }}</span>
                    <span class="text-[10px] text-slate-400">{{ Auth::user()->isBranchUser() ? (Auth::user()->branch->name ?? 'Şube Personeli') : (Auth::user()->company_name ?? 'Bayi Ana Hesabı') }}</span>
                </div>
            </div>
        </div>
    </div>

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

                <!-- Price & Purchase Action -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-[11px] text-slate-400 font-bold uppercase tracking-wider">Satış Fiyatı</div>
                        <div class="text-2xl font-black text-slate-900">€{{ number_format($assignment->sale_price, 2) }}</div>
                    </div>

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

        <!-- Account Balance Summary -->
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
            @elseif(Auth::user()->isBranchUser())
                <div class="p-2.5 bg-indigo-50 border border-indigo-200 rounded-xl text-xs text-indigo-900 font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-store text-indigo-600"></i>
                    <span>İşlem Şubesi: <strong>{{ Auth::user()->branch->name ?? 'Şubeniz' }}</strong></span>
                </div>
            @endif

            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2">
                <i class="fa-solid fa-circle-info text-blue-600 text-sm shrink-0 mt-0.5"></i>
                <span>Satış onaylandığı anda paket ücreti bakiyenizden tahsil edilecek ve müşterinize ait eSIM QR kodu anında hazır olarak açılacaktır.</span>
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
        const price = btn.getAttribute('data-price');
        const userBalance = {{ (float) $effectiveCustomer->balance }};
        
        document.getElementById('modalCustomerPackageId').value = packageId;
        document.getElementById('modalPackName').innerText = packName;
        document.getElementById('modalPackPrice').innerText = parseFloat(price).toFixed(2);

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

        const modal = document.getElementById('checkoutModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
</script>
@endpush
@endsection
