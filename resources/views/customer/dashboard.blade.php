@extends('layouts.app')

@section('title', 'Müşteri eSIM Portalı — POLO SIM')

@section('content')
<div class="space-y-8">
    <!-- Top Greeting Header & Customer Balance Card -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-qrcode text-blue-600"></i>
                <span>Hoş Geldiniz, {{ Auth::user()->name }}</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">Yalnızca size özel atanan eSIM paketlerini bakiyenizle anında satın alabilir, QR kod ve aktivasyon detaylarına erişebilirsiniz.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="glass-panel px-5 py-3 rounded-2xl border-l-4 border-cyan-500 flex items-center gap-3 shadow-md">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg font-bold shrink-0 border border-cyan-200">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Hesap Bakiyeniz</span>
                    <div class="text-xl font-black text-cyan-600 leading-tight">₺{{ number_format(Auth::user()->balance, 2) }}</div>
                </div>
            </div>

            <div class="hidden sm:flex glass-card px-4 py-3 rounded-2xl text-xs text-slate-600 border border-slate-200 items-center gap-2 shadow-sm">
                <i class="fa-solid fa-building text-blue-600"></i>
                <span class="font-semibold">{{ Auth::user()->company_name ?? 'Bireysel Müşteri' }}</span>
            </div>
        </div>
    </div>

    <!-- Main Navigation Tabs & Branch Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-200 gap-4" id="portalTabs">
        <div class="flex gap-4">
            <button onclick="switchTab('store')" id="tabBtn-store" class="py-3 px-4 font-bold text-sm border-b-2 border-blue-600 text-blue-600 transition flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Bana Özel eSIM Paketleri</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 font-extrabold">{{ count($assignedPackages) }}</span>
            </button>
            <button onclick="switchTab('myEsims')" id="tabBtn-myEsims" class="py-3 px-4 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-900 transition flex items-center gap-2">
                <i class="fa-solid fa-sim-card"></i>
                <span>Satın Alınan eSIM'lerim</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700 font-extrabold">{{ count($orders) }}</span>
            </button>
        </div>

        @if(!Auth::user()->isBranchUser() && count($branches) > 0)
            <!-- Dealer Admin Branch Filter -->
            <form method="GET" action="{{ route('customer.dashboard') }}" class="flex items-center gap-2 pb-2 md:pb-0">
                <i class="fa-solid fa-filter text-slate-400 text-xs"></i>
                <select name="branch_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 shadow-sm">
                    <option value="">-- Tüm Şubelerin Satışları --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
                @if($selectedBranchId)
                    <a href="{{ route('customer.dashboard') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs rounded-xl font-bold border border-slate-300">Temizle</a>
                @endif
            </form>
        @endif
    </div>

    @if(!Auth::user()->isBranchUser() && count($branchStats) > 0)
        <!-- Dealer Admin Branch Performance Cards -->
        <div class="space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-store text-blue-600"></i>
                <span>Şubelerinizin Satış Performans Özeti</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4">
                @foreach($branchStats as $stat)
                    <div class="glass-card p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-sm text-slate-900 truncate">{{ $stat->branch_name ?? 'Merkez / Genel' }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $stat->total_orders }} Sipariş
                            </span>
                        </div>
                        <div class="text-base font-black text-slate-800">
                            ₺{{ number_format($stat->total_spent, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tab 1: Available Assigned Packages Store -->
    <div id="tabContent-store" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($assignedPackages as $assignment)
                @php $product = $assignment->product; @endphp
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between space-y-5 hover:border-blue-300 transition shadow-sm relative group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $product->product_type === 'DAILY_PACK' ? 'Günlük Paket' : 'Veri Paketi' }}
                            </span>
                            <span class="text-xs font-mono text-slate-400 font-semibold">{{ $product->card_type ?? 'eSIM' }}</span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 leading-snug group-hover:text-blue-600 transition">
                            {{ $product->product_name }}
                        </h3>

                        <!-- Supported Countries -->
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($product->country_code_list ?? [], 0, 5) as $code)
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-xs font-mono text-slate-700 border border-slate-200 font-semibold">{{ $code }}</span>
                            @endforeach
                            @if(count($product->country_code_list ?? []) > 5)
                                <span class="text-xs text-slate-400 font-medium align-middle">+{{ count($product->country_code_list) - 5 }} ülke</span>
                            @endif
                        </div>

                        <!-- Package Metrics -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <div class="text-xs text-slate-500 font-medium">Veri Kotası</div>
                                <div class="text-base font-extrabold text-blue-700">{{ $product->data_total }} {{ $product->data_unit }}</div>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                                <div class="text-xs text-slate-500 font-medium">Süre</div>
                                <div class="text-base font-extrabold text-slate-800">{{ $product->usage_period }} Gün</div>
                            </div>
                        </div>
                    </div>

                    <!-- Price & Purchase Button -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-400 font-medium">Fiyat</div>
                            <div class="text-2xl font-black text-slate-900">₺{{ number_format($assignment->sale_price, 2) }}</div>
                        </div>

                        <button type="button" 
                            data-package-id="{{ $assignment->id }}"
                            data-name="{{ $product->product_name }}"
                            data-price="{{ number_format($assignment->sale_price, 2) }}"
                            data-data="{{ $product->data_total }} {{ $product->data_unit }}"
                            data-period="{{ $product->usage_period }} Gün"
                            onclick="openPaymentModalFromBtn(this)"
                            class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
                            <i class="fa-solid fa-wallet"></i>
                            <span>Bakiyem İle Satın Al</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full glass-panel p-12 text-center text-slate-500 space-y-3">
                    <i class="fa-solid fa-box-open text-4xl text-slate-400"></i>
                    <p class="text-base font-semibold text-slate-700">Henüz size tanımlanmış özel bir eSIM paketi bulunmuyor.</p>
                    <p class="text-xs text-slate-400">Yöneticiniz sizin için paket tanımladığında bu alanda görünecektir.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 2: Purchased eSIMs List -->
    <div id="tabContent-myEsims" class="space-y-6 hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($orders as $order)
                @php $product = $order->product; @endphp
                <div class="glass-panel p-6 rounded-2xl space-y-5 border-l-4 border-emerald-500 relative shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $order->order_status }}
                            </span>
                            <h3 class="text-lg font-bold text-slate-900 mt-2">{{ $product->product_name ?? 'eSIM Paket' }}</h3>
                            <div class="text-xs text-slate-400 font-mono mt-0.5">Sipariş: {{ $order->order_no ?? $order->channel_order_no }}</div>
                        </div>

                        <!-- 1-Click QR Modal Opener -->
                        <button onclick="showQrModal('{{ $order->qr_code }}', '{{ $order->iccid }}', '{{ $order->apple_install_url }}')" 
                            class="p-3 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-blue-200 text-center transition flex flex-col items-center gap-1 active:scale-95" title="QR Kodu Büyüt">
                            <i class="fa-solid fa-qrcode text-2xl"></i>
                            <span class="text-[10px] font-bold uppercase">QR Göster</span>
                        </button>
                    </div>

                    <!-- ICCID & Details Box -->
                    <div class="bg-slate-50 p-4 rounded-xl space-y-2 border border-slate-200">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">İşlem Yapan Şube:</span>
                            <span class="px-2 py-0.5 rounded-md font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i class="fa-solid fa-store text-[10px] mr-1"></i>{{ $order->branch_name ?? 'Merkez / Genel' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/60">
                            <span class="text-slate-500 font-medium">ICCID Numarası:</span>
                            <button onclick="copyToClipboard('{{ $order->iccid }}')" class="text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-copy"></i> Kopyala
                            </button>
                        </div>
                        <div class="font-mono text-sm text-slate-900 font-extrabold tracking-wider break-all">
                            {{ $order->iccid ?? 'Atanıyor...' }}
                        </div>
                    </div>

                    <!-- iOS 1-Tap Install Link & Live Usage Check -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        @if($order->apple_install_url)
                            <a href="{{ $order->apple_install_url }}" target="_blank" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl border border-slate-300 text-center transition flex items-center justify-center gap-2">
                                <i class="fa-brands fa-apple text-base"></i>
                                <span>iOS Tek Tık Kurulum</span>
                            </a>
                        @endif

                        <button onclick="checkLiveUsage('{{ $order->id }}')" class="px-3.5 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-200 text-center transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Canlı Kota Sorgula</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full glass-panel p-12 text-center text-slate-500 space-y-3">
                    <i class="fa-solid fa-sim-card text-4xl text-slate-400"></i>
                    <p class="text-base font-semibold text-slate-700">Henüz satın alınmış bir eSIM paketiniz yok.</p>
                    <p class="text-xs text-slate-400">"Bana Özel eSIM Paketleri" sekmesinden ilk paketinizi satın alabilirsiniz.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal: Account Balance Checkout Modal -->
<div id="checkoutModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 overflow-y-auto">
    <div class="bg-white max-w-lg w-full p-6 md:p-8 rounded-3xl shadow-2xl space-y-5 relative border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-wallet text-cyan-600"></i>
                <span>Bakiye İle Paket Satın Alma</span>
            </h3>
            <button onclick="document.getElementById('checkoutModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
        </div>

        <!-- Account Balance Summary -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>Mevcut Bakiyeniz:</span>
                <span class="font-extrabold text-base text-cyan-700">₺{{ number_format(Auth::user()->balance, 2) }}</span>
            </div>
            
            <div class="flex items-center justify-between text-xs text-slate-500 border-t border-slate-200/80 pt-2">
                <span>Seçilen Paket Adı:</span>
                <span class="font-bold text-slate-900" id="modalPackName">Paket Adı</span>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-500">
                <span>Paket Satış Tutarı:</span>
                <span class="font-bold text-rose-600">-₺<span id="modalPackPrice">0.00</span></span>
            </div>

            <div class="flex items-center justify-between text-xs text-slate-700 border-t border-slate-200/80 pt-2 font-bold">
                <span>Satın Alma Sonrası Kalan Bakiye:</span>
                <span class="font-black text-emerald-600 text-sm">₺<span id="modalRemainingPrice">0.00</span></span>
            </div>
        </div>

        <!-- Purchase Confirmation Form -->
        <form action="{{ route('customer.buy') }}" method="POST" id="checkoutForm" class="space-y-4">
            @csrf
            <input type="hidden" name="customer_package_id" id="modalCustomerPackageId">

            @if(count($branches) > 0)
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">İşlem Yapılan Şube</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600 text-sm">
                        <option value="">-- Merkez / Genel --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2">
                <i class="fa-solid fa-circle-info text-blue-600 text-base shrink-0 mt-0.5"></i>
                <span>Satın alma onaylandığında paket ücreti bakiyenizden düşülecek ve eSIM QR kodunuz ile aktivasyon bilgileri anında ekranınıza yüklenecektir.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('checkoutModal').classList.add('hidden')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-xl font-bold">İptal</button>
                <button type="submit" id="paySubmitBtn"
                    class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2 text-sm active:scale-95">
                    <i class="fa-solid fa-check-double"></i>
                    <span>Bakiyem İle Satın Al</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: QR Code Display -->
<div id="qrModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-sm w-full p-6 rounded-3xl shadow-2xl text-center space-y-4 relative border border-slate-200">
        <button onclick="document.getElementById('qrModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        
        <h3 class="text-lg font-bold text-slate-900 flex items-center justify-center gap-2">
            <i class="fa-solid fa-qrcode text-blue-600"></i>
            <span>eSIM QR Kodunuz</span>
        </h3>

        <!-- QR Code Container -->
        <div class="p-4 bg-white rounded-2xl mx-auto inline-block border border-slate-200 shadow-md">
            <div id="qrcodeDiv"></div>
        </div>

        <div class="text-xs text-slate-600 space-y-1">
            <p class="font-bold text-slate-800">Cihazınızdan Ayarlar > Hücresel > eSIM Ekle adımlarını takip ederek QR kodu taratın.</p>
            <p class="font-mono text-[11px] text-blue-600 break-all font-semibold" id="qrTextValue"></p>
        </div>

        <div class="pt-3 border-t border-slate-100 space-y-2">
            <a id="modalAppleBtn" href="#" target="_blank" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-sm">
                <i class="fa-brands fa-apple text-base"></i>
                <span>iOS Direkt Yükle</span>
            </a>
        </div>
    </div>
</div>

<!-- Modal: Live Usage Check -->
<div id="usageModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-md w-full p-6 rounded-3xl shadow-2xl space-y-4 relative border border-slate-200">
        <button onclick="document.getElementById('usageModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        
        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-cyan-600"></i>
            <span>Canlı eSIM Kullanım Durumu</span>
        </h3>

        <div id="usageContent" class="space-y-4">
            <div class="text-center py-6 text-slate-500">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                <p>API sunucularından anlık veri çekiliyor...</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tab) {
        document.getElementById('tabContent-store').classList.add('hidden');
        document.getElementById('tabContent-myEsims').classList.add('hidden');

        document.getElementById('tabBtn-store').className = 'py-3 px-4 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-900 transition flex items-center gap-2';
        document.getElementById('tabBtn-myEsims').className = 'py-3 px-4 font-bold text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-900 transition flex items-center gap-2';

        if (tab === 'store') {
            document.getElementById('tabContent-store').classList.remove('hidden');
            document.getElementById('tabBtn-store').className = 'py-3 px-4 font-bold text-sm border-b-2 border-blue-600 text-blue-600 transition flex items-center gap-2';
        } else {
            document.getElementById('tabContent-myEsims').classList.remove('hidden');
            document.getElementById('tabBtn-myEsims').className = 'py-3 px-4 font-bold text-sm border-b-2 border-blue-600 text-blue-600 transition flex items-center gap-2';
        }
    }

    function openPaymentModalFromBtn(btn) {
        const packageId = btn.getAttribute('data-package-id');
        const packName = btn.getAttribute('data-name');
        const price = btn.getAttribute('data-price');
        const userBalance = {{ (float) Auth::user()->balance }};
        
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

    function showQrModal(qrCode, iccid, appleUrl) {
        const qrContainer = document.getElementById('qrcodeDiv');
        qrContainer.innerHTML = '';

        if (qrCode) {
            new QRCode(qrContainer, {
                text: qrCode,
                width: 200,
                height: 200,
            });
            document.getElementById('qrTextValue').innerText = qrCode;
        } else {
            qrContainer.innerHTML = '<span class="text-xs text-rose-600 font-bold">QR kod üretilemedi</span>';
        }

        const appleBtn = document.getElementById('modalAppleBtn');
        if (appleUrl && appleUrl !== 'null') {
            appleBtn.href = appleUrl;
            appleBtn.classList.remove('hidden');
        } else {
            appleBtn.classList.add('hidden');
        }

        document.getElementById('qrModal').classList.remove('hidden');
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            Swal.fire({
                title: 'Kopyalandı!',
                text: 'ICCID panoya kopyalandı: ' + text,
                icon: 'success',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                background: '#ffffff',
                color: '#0f172a'
            });
        });
    }

    function checkLiveUsage(orderId) {
        document.getElementById('usageModal').classList.remove('hidden');
        const content = document.getElementById('usageContent');
        content.innerHTML = `
            <div class="text-center py-6 text-slate-500">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                <p class="text-sm font-medium">API sunucularından canlı veriler sorgulanıyor...</p>
            </div>
        `;

        fetch('/customer/orders/' + orderId + '/usage')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const usage = data.usage || {};
                    const profile = data.profile || {};
                    content.innerHTML = `
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3 text-sm">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-200">
                                <span class="text-slate-500 font-medium">Profil Durumu:</span>
                                <span class="font-extrabold text-emerald-600 uppercase">${profile.state || 'Aktif'}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Toplam Kota:</span>
                                <span class="font-bold text-slate-900">${usage.dataTotal || 0} MB</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 font-medium">Kullanılan Veri:</span>
                                <span class="font-bold text-rose-600">${usage.dataUsage || 0} MB</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                                <span class="text-slate-700 font-bold">Kalan Kullanılabilir Veri:</span>
                                <span class="font-black text-cyan-600 text-base">${usage.dataResidual || 0} MB</span>
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<p class="text-rose-600 text-center py-4 font-bold">Veri sorgulanamadı.</p>';
                }
            })
            .catch(err => {
                content.innerHTML = '<p class="text-rose-600 text-center py-4 font-bold">Bağlantı hatası oluştu.</p>';
            });
    }
</script>
@endpush
@endsection
