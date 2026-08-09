@extends('layouts.app')

@section('title', 'Müşteri eSIM Portalı — POLO SIM')

@section('content')
<div class="space-y-8">
    <!-- Top Greeting Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-qrcode text-blue-400"></i>
                <span>Hoş Geldiniz, {{ Auth::user()->name }}</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Size özel tanımlanmış eSIM paketlerini kredi kartı ile güvenle satın alabilir, QR kod ve ICCID bilgilerinize erişebilirsiniz.</p>
        </div>
        <div class="glass-card px-4 py-2 rounded-xl text-xs text-slate-300 border border-slate-700 flex items-center gap-2">
            <i class="fa-solid fa-building text-blue-400"></i>
            <span>{{ Auth::user()->company_name ?? 'Bireysel Müşteri' }}</span>
        </div>
    </div>

    <!-- Main Navigation Tabs -->
    <div class="flex border-b border-slate-800 gap-4" id="portalTabs">
        <button onclick="switchTab('store')" id="tabBtn-store" class="py-3 px-4 font-bold text-sm border-b-2 border-blue-500 text-blue-400 transition flex items-center gap-2">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Bana Özel eSIM Paketleri</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-blue-500/20 text-blue-300">{{ count($assignedPackages) }}</span>
        </button>
        <button onclick="switchTab('myEsims')" id="tabBtn-myEsims" class="py-3 px-4 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition flex items-center gap-2">
            <i class="fa-solid fa-sim-card"></i>
            <span>Satın Alınan eSIM'lerim</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-purple-500/20 text-purple-300">{{ count($orders) }}</span>
        </button>
    </div>

    <!-- Tab 1: Available Assigned Packages Store -->
    <div id="tabContent-store" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($assignedPackages as $assignment)
                @php $product = $assignment->product; @endphp
                <div class="glass-card rounded-2xl p-6 flex flex-col justify-between space-y-5 hover:border-blue-500/40 transition glow-blue relative group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                {{ $product->product_type === 'DAILY_PACK' ? 'Günlük Paket' : 'Veri Paketi' }}
                            </span>
                            <span class="text-xs font-mono text-slate-500">{{ $product->card_type ?? 'eSIM' }}</span>
                        </div>

                        <h3 class="text-lg font-bold text-white leading-snug group-hover:text-blue-400 transition">
                            {{ $product->product_name }}
                        </h3>

                        <!-- Supported Countries -->
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($product->country_code_list ?? [], 0, 5) as $code)
                                <span class="px-2 py-0.5 bg-slate-900 rounded text-xs font-mono text-slate-300 border border-slate-700/80">{{ $code }}</span>
                            @endforeach
                            @if(count($product->country_code_list ?? []) > 5)
                                <span class="text-xs text-slate-500 align-middle">+{{ count($product->country_code_list) - 5 }} ülke</span>
                            @endif
                        </div>

                        <!-- Package Metrics -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800 text-center">
                                <div class="text-xs text-slate-400">Veri Kotası</div>
                                <div class="text-base font-bold text-cyan-400">{{ $product->data_total }} {{ $product->data_unit }}</div>
                            </div>
                            <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-800 text-center">
                                <div class="text-xs text-slate-400">Süre</div>
                                <div class="text-base font-bold text-slate-200">{{ $product->usage_period }} Gün</div>
                            </div>
                        </div>
                    </div>

                    <!-- Price & Visa/Mastercard Payment Trigger Button -->
                    <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-slate-400">Fiyat</div>
                            <div class="text-2xl font-extrabold text-white">₺{{ number_format($assignment->sale_price, 2) }}</div>
                        </div>

                        <button type="button" 
                            data-package-id="{{ $assignment->id }}"
                            data-name="{{ $product->product_name }}"
                            data-price="{{ number_format($assignment->sale_price, 2) }}"
                            data-data="{{ $product->data_total }} {{ $product->data_unit }}"
                            data-period="{{ $product->usage_period }} Gün"
                            onclick="openPaymentModalFromBtn(this)"
                            class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-sm rounded-xl shadow-lg glow-blue transition flex items-center gap-2">
                            <i class="fa-solid fa-credit-card"></i>
                            <span>Kart ile Satın Al</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full glass-panel p-12 text-center text-slate-400 space-y-3">
                    <i class="fa-solid fa-box-open text-4xl text-slate-600"></i>
                    <p class="text-base font-medium text-slate-300">Henüz size tanımlanmış özel bir eSIM paketi bulunmuyor.</p>
                    <p class="text-xs text-slate-500">Yöneticiniz sizin için paket tanımladığında bu alanda görünecektir.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Tab 2: Purchased eSIMs List -->
    <div id="tabContent-myEsims" class="space-y-6 hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($orders as $order)
                @php $product = $order->product; @endphp
                <div class="glass-panel p-6 rounded-2xl space-y-5 border-l-4 border-emerald-500 relative">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                {{ $order->order_status }}
                            </span>
                            <h3 class="text-lg font-bold text-white mt-2">{{ $product->product_name ?? 'eSIM Paket' }}</h3>
                            <div class="text-xs text-slate-400 font-mono mt-0.5">Sipariş: {{ $order->order_no ?? $order->channel_order_no }}</div>
                        </div>

                        <!-- 1-Click QR Modal Opener -->
                        <button onclick="showQrModal('{{ $order->qr_code }}', '{{ $order->iccid }}', '{{ $order->apple_install_url }}')" 
                            class="p-3 bg-slate-900 hover:bg-slate-800 text-blue-400 rounded-xl border border-blue-500/30 text-center transition flex flex-col items-center gap-1 group" title="QR Kodu Büyüt">
                            <i class="fa-solid fa-qrcode text-2xl group-hover:scale-110 transition transform"></i>
                            <span class="text-[10px] font-semibold uppercase">QR Göster</span>
                        </button>
                    </div>

                    <!-- ICCID & Details Box -->
                    <div class="bg-slate-900/90 p-4 rounded-xl space-y-2 border border-slate-800">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400 font-medium">ICCID Numarası:</span>
                            <button onclick="copyToClipboard('{{ $order->iccid }}')" class="text-blue-400 hover:text-white font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-copy"></i> Kopyala
                            </button>
                        </div>
                        <div class="font-mono text-sm text-emerald-300 font-bold tracking-wider break-all">
                            {{ $order->iccid ?? 'Atanıyor...' }}
                        </div>
                    </div>

                    <!-- iOS 1-Tap Install Link & Live Usage Check -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        @if($order->apple_install_url)
                            <a href="{{ $order->apple_install_url }}" target="_blank" class="px-3.5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700 text-center transition flex items-center justify-center gap-2">
                                <i class="fa-brands fa-apple text-base"></i>
                                <span>iOS Tek Tık Kurulum</span>
                            </a>
                        @endif

                        <button onclick="checkLiveUsage('{{ $order->id }}')" class="px-3.5 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 text-xs font-semibold rounded-xl border border-blue-500/30 text-center transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Canlı Kota Sorgula</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full glass-panel p-12 text-center text-slate-400 space-y-3">
                    <i class="fa-solid fa-sim-card text-4xl text-slate-600"></i>
                    <p class="text-base font-medium text-slate-300">Henüz satın alınmış bir eSIM paketiniz yok.</p>
                    <p class="text-xs text-slate-500">"Bana Özel eSIM Paketleri" sekmesinden ilk paketinizi satın alabilirsiniz.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal: Visa / Mastercard Kredi Kartı Ödeme Ekranı -->
<div id="paymentModal" class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md flex items-center justify-center hidden p-4 overflow-y-auto">
    <div class="glass-panel max-w-xl w-full p-6 md:p-8 rounded-3xl shadow-2xl space-y-6 relative border border-slate-700/80">
        <button onclick="closePaymentModal()" class="absolute top-5 right-5 text-slate-400 hover:text-white text-2xl transition">&times;</button>
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div>
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-blue-400"></i>
                    <span>Güvenli Kredi Kartı Ödemesi</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">256-bit SSL ve 3D Secure Güvenlik Korumalı</p>
            </div>
            <!-- Visa & Mastercard Logos -->
            <div class="flex items-center gap-2 text-2xl text-slate-300">
                <i class="fa-brands fa-cc-visa text-blue-400" title="Visa"></i>
                <i class="fa-brands fa-cc-mastercard text-amber-500" title="Mastercard"></i>
            </div>
        </div>

        <!-- Interactive Virtual Credit Card Graphic -->
        <div class="w-full h-48 rounded-2xl bg-gradient-to-tr from-slate-900 via-indigo-950 to-blue-900 p-5 border border-slate-700/80 shadow-2xl relative overflow-hidden flex flex-col justify-between text-white">
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl"></div>
            
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-7 bg-amber-400/90 rounded-md border border-amber-300/50 flex items-center justify-center">
                        <div class="w-6 h-4 border border-amber-600/60 rounded"></div>
                    </div>
                    <span class="text-xs font-mono tracking-widest text-slate-300">DEBIT / CREDIT</span>
                </div>
                <div id="cardBrandLogo" class="text-2xl font-bold italic tracking-wider text-slate-200">
                    <i class="fa-brands fa-cc-visa text-blue-400"></i>
                </div>
            </div>

            <!-- Card Number Preview -->
            <div class="font-mono text-xl md:text-2xl tracking-[0.2em] font-extrabold text-slate-100" id="cardNumPreview">
                •••• •••• •••• ••••
            </div>

            <!-- Cardholder & Expiry Preview -->
            <div class="flex justify-between items-end text-xs uppercase font-mono">
                <div>
                    <div class="text-[9px] text-slate-400 uppercase">Kart Sahibi</div>
                    <div class="font-bold tracking-wider text-slate-200" id="cardHolderPreview">AD SOYAD</div>
                </div>
                <div>
                    <div class="text-[9px] text-slate-400 uppercase">Son Kullanma</div>
                    <div class="font-bold text-slate-200" id="cardExpPreview">MM/YY</div>
                </div>
            </div>
        </div>

        <!-- Order Summary Pill -->
        <div class="bg-slate-900/90 p-4 rounded-xl border border-slate-800 flex items-center justify-between text-xs">
            <div>
                <div class="font-semibold text-white" id="modalPackName">Paket Adı</div>
                <div class="text-slate-400"><span id="modalPackData"></span> — <span id="modalPackPeriod"></span></div>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-slate-400 uppercase font-semibold">Toplam Ödenecek</div>
                <div class="text-xl font-extrabold text-emerald-400">₺<span id="modalPackPrice">0.00</span></div>
            </div>
        </div>

        <!-- Checkout Payment Form -->
        <form action="{{ route('customer.buy') }}" method="POST" id="checkoutForm" class="space-y-4" onsubmit="handlePaymentSubmit(event)">
            @csrf
            <input type="hidden" name="customer_package_id" id="modalCustomerPackageId">

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kart Üzerindeki İsim</label>
                <input type="text" id="cardHolderInput" required placeholder="AHMET YILMAZ" oninput="updateCardGraphic()"
                    class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono uppercase text-sm focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Kart Numarası (Visa / Mastercard)</label>
                <div class="relative">
                    <input type="text" id="cardNumInput" maxlength="19" required placeholder="5549 1234 5678 9012" oninput="formatCardNum(this); updateCardGraphic();"
                        class="w-full pl-3.5 pr-12 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-blue-500">
                    <span id="inputBrandIcon" class="absolute right-3 top-2.5 text-xl text-slate-400">
                        <i class="fa-solid fa-credit-card"></i>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Son Kullanma (AY/YIL)</label>
                    <input type="text" id="cardExpInput" maxlength="5" required placeholder="12/28" oninput="formatExp(this); updateCardGraphic();"
                        class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-sm text-center focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Güvenlik Kodu (CVC/CVV)</label>
                    <input type="password" maxlength="3" required placeholder="•••"
                        class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-sm text-center focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <!-- Demo Auto-Fill Preset Button -->
            <div class="pt-1 flex items-center justify-between text-xs text-slate-400">
                <span>3D Secure 2.0 Doğrulamalı İşlem</span>
                <button type="button" onclick="autoFillDemoCard()" class="text-blue-400 hover:text-white font-medium underline">
                    ⚡ Hızlı Test Kartı Doldur
                </button>
            </div>

            <!-- Action Button -->
            <div class="pt-2">
                <button type="submit" id="paySubmitBtn"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-blue-600 hover:from-emerald-500 hover:to-blue-500 text-white font-bold rounded-xl shadow-lg glow-emerald transition flex items-center justify-center gap-2 text-base">
                    <i class="fa-solid fa-lock"></i>
                    <span>₺<span id="btnPayPrice">0.00</span> Ödeme Yap ve eSIM'i Aktif Et</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: QR Code Display -->
<div id="qrModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center hidden p-4">
    <div class="glass-panel max-w-sm w-full p-6 rounded-2xl shadow-2xl text-center space-y-4 relative">
        <button onclick="document.getElementById('qrModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-white text-xl">&times;</button>
        
        <h3 class="text-lg font-bold text-white flex items-center justify-center gap-2">
            <i class="fa-solid fa-qrcode text-blue-400"></i>
            <span>eSIM QR Kodunuz</span>
        </h3>

        <!-- QR Code Container -->
        <div class="p-4 bg-white rounded-2xl mx-auto inline-block shadow-xl glow-blue">
            <div id="qrcodeDiv"></div>
        </div>

        <div class="text-xs text-slate-400 space-y-1">
            <p class="font-semibold text-slate-200">Cihazınızdan Ayarlar > Hücresel > eSIM Ekle adımlarını takip ederek QR kodu taratın.</p>
            <p class="font-mono text-[11px] text-blue-400 break-all" id="qrTextValue"></p>
        </div>

        <div class="pt-3 border-t border-slate-800 space-y-2">
            <a id="modalAppleBtn" href="#" target="_blank" class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl border border-slate-700 transition flex items-center justify-center gap-2">
                <i class="fa-brands fa-apple text-base"></i>
                <span>iOS Direkt Yükle</span>
            </a>
        </div>
    </div>
</div>

<!-- Modal: Live Usage Check -->
<div id="usageModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center hidden p-4">
    <div class="glass-panel max-w-md w-full p-6 rounded-2xl shadow-2xl space-y-4 relative">
        <button onclick="document.getElementById('usageModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-white text-xl">&times;</button>
        
        <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-chart-pie text-cyan-400"></i>
            <span>Canlı eSIM Kullanım Durumu</span>
        </h3>

        <div id="usageContent" class="space-y-4">
            <div class="text-center py-6 text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-400 mb-2"></i>
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

        document.getElementById('tabBtn-store').className = 'py-3 px-4 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition flex items-center gap-2';
        document.getElementById('tabBtn-myEsims').className = 'py-3 px-4 font-bold text-sm border-b-2 border-transparent text-slate-400 hover:text-slate-200 transition flex items-center gap-2';

        if (tab === 'store') {
            document.getElementById('tabContent-store').classList.remove('hidden');
            document.getElementById('tabBtn-store').className = 'py-3 px-4 font-bold text-sm border-b-2 border-blue-500 text-blue-400 transition flex items-center gap-2';
        } else {
            document.getElementById('tabContent-myEsims').classList.remove('hidden');
            document.getElementById('tabBtn-myEsims').className = 'py-3 px-4 font-bold text-sm border-b-2 border-blue-500 text-blue-400 transition flex items-center gap-2';
        }
    }

    function openPaymentModalFromBtn(btn) {
        const packageId = btn.getAttribute('data-package-id');
        const name = btn.getAttribute('data-name');
        const price = btn.getAttribute('data-price');
        const data = btn.getAttribute('data-data');
        const period = btn.getAttribute('data-period');

        document.getElementById('modalCustomerPackageId').value = packageId;
        document.getElementById('modalPackName').innerText = name;
        document.getElementById('modalPackPrice').innerText = price;
        document.getElementById('btnPayPrice').innerText = price;
        document.getElementById('modalPackData').innerText = data;
        document.getElementById('modalPackPeriod').innerText = period;

        document.getElementById('paymentModal').classList.remove('hidden');
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function formatCardNum(input) {
        let val = input.value.replace(/\D/g, '');
        let formatted = '';
        for (let i = 0; i < val.length; i++) {
            if (i > 0 && i % 4 === 0) formatted += ' ';
            formatted += val[i];
        }
        input.value = formatted;
    }

    function formatExp(input) {
        let val = input.value.replace(/\D/g, '');
        if (val.length >= 2) {
            input.value = val.substring(0, 2) + '/' + val.substring(2, 4);
        } else {
            input.value = val;
        }
    }

    function updateCardGraphic() {
        const holder = document.getElementById('cardHolderInput').value;
        const num = document.getElementById('cardNumInput').value;
        const exp = document.getElementById('cardExpInput').value;

        document.getElementById('cardHolderPreview').innerText = holder ? holder.toUpperCase() : 'AD SOYAD';
        document.getElementById('cardNumPreview').innerText = num ? num : '•••• •••• •••• ••••';
        document.getElementById('cardExpPreview').innerText = exp ? exp : 'MM/YY';

        // Detect Card Brand (Visa or Mastercard)
        const brandLogo = document.getElementById('cardBrandLogo');
        const inputIcon = document.getElementById('inputBrandIcon');
        const cleanNum = num.replace(/\s/g, '');

        if (cleanNum.startsWith('4')) {
            brandLogo.innerHTML = '<i class="fa-brands fa-cc-visa text-blue-400"></i>';
            inputIcon.innerHTML = '<i class="fa-brands fa-cc-visa text-blue-400"></i>';
        } else if (/^(5[1-5]|2[2-7])/.test(cleanNum)) {
            brandLogo.innerHTML = '<i class="fa-brands fa-cc-mastercard text-amber-500"></i>';
            inputIcon.innerHTML = '<i class="fa-brands fa-cc-mastercard text-amber-500"></i>';
        } else {
            brandLogo.innerHTML = '<i class="fa-solid fa-credit-card text-slate-300"></i>';
            inputIcon.innerHTML = '<i class="fa-solid fa-credit-card text-slate-400"></i>';
        }
    }

    function autoFillDemoCard() {
        document.getElementById('cardHolderInput').value = 'AHMET YILMAZ';
        document.getElementById('cardNumInput').value = '5549 1234 5678 9012';
        document.getElementById('cardExpInput').value = '12/28';
        updateCardGraphic();
    }

    function handlePaymentSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('paySubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>3D Secure 2.0 Ödeme Doğrulanıyor...</span>';

        setTimeout(() => {
            document.getElementById('checkoutForm').submit();
        }, 1000);
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
            qrContainer.innerHTML = '<span class="text-xs text-red-500">QR kod üretilemedi</span>';
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
                background: '#0f172a',
                color: '#fff'
            });
        });
    }

    function checkLiveUsage(orderId) {
        document.getElementById('usageModal').classList.remove('hidden');
        const content = document.getElementById('usageContent');
        content.innerHTML = `
            <div class="text-center py-6 text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-400 mb-2"></i>
                <p>API sunucularından canlı veriler sorgulanıyor...</p>
            </div>
        `;

        fetch('/customer/orders/' + orderId + '/usage')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const usage = data.usage || {};
                    const profile = data.profile || {};
                    content.innerHTML = `
                        <div class="bg-slate-900/80 p-4 rounded-xl border border-slate-800 space-y-3 text-sm">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-800">
                                <span class="text-slate-400">Profil Durumu:</span>
                                <span class="font-bold text-emerald-400 uppercase">${profile.state || 'Aktif'}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Toplam Kota:</span>
                                <span class="font-bold text-white">${usage.dataTotal || 0} MB</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Kullanılan Veri:</span>
                                <span class="font-bold text-rose-400">${usage.dataUsage || 0} MB</span>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-slate-800">
                                <span class="text-slate-400 font-semibold">Kalan Kullanılabilir Veri:</span>
                                <span class="font-extrabold text-cyan-300 text-base">${usage.dataResidual || 0} MB</span>
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<p class="text-rose-400 text-center py-4">Veri sorgulanamadı.</p>';
                }
            })
            .catch(err => {
                content.innerHTML = '<p class="text-rose-400 text-center py-4">Bağlantı hatası oluştu.</p>';
            });
    }
</script>
@endpush
@endsection
