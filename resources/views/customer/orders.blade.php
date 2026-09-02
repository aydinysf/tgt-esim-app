@extends('layouts.app')

@section('title', 'Satılan Paketler & eSIM Siparişleri — POLO SIM')

@section('content')
<div class="space-y-6">
    <!-- Top Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-box-archive text-purple-600"></i>
                <span>Satılan Paketler & eSIM Siparişleri</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                @if(Auth::user()->isBranchUser())
                    <span><strong>{{ Auth::user()->branch->name ?? 'Şubeniz' }}</strong> tarafından satışı yapılmış tüm aktif eSIM paketleri ve QR kodları.</span>
                @else
                    <span>Tüm şubeleriniz tarafından satışı yapılmış paketleri listeleyin, QR kodlarına ve anlık kotalarına erişin.</span>
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('customer.dashboard') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center gap-2 active:scale-95">
                <i class="fa-solid fa-cart-plus text-sm"></i>
                <span>Yeni eSIM Paketi Sat</span>
            </a>
        </div>
    </div>

    @if(!Auth::user()->isBranchUser() && count($branchStats) > 0)
        <!-- Dealer Owner Branch Performance Summary -->
        <div class="space-y-3">
            <div class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                <i class="fa-solid fa-store text-indigo-600"></i>
                <span>Şubelerinizin Satış Özeti</span>
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
                            €{{ number_format($stat->total_spent, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Search and Branch Filter Bar -->
    <div class="glass-card p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('customer.orders.index') }}" class="w-full md:w-auto flex-1 flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full sm:w-80">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="ICCID, Sipariş No veya Paket Ara..." 
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-blue-600 focus:bg-white transition">
            </div>

            @if(!Auth::user()->isBranchUser() && count($branches) > 0)
                <select name="branch_id" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600">
                    <option value="">-- Tüm Şubelerin Satışları --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ ($selectedBranchId ?? '') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition">
                Filtrele
            </button>

            @if(!empty($search) || !empty($selectedBranchId))
                <a href="{{ route('customer.orders.index') }}" class="w-full sm:w-auto px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl text-center">
                    Temizle
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500 font-semibold self-end md:self-auto">
            Toplam: <span class="font-extrabold text-slate-900">{{ $orders->total() }}</span> Satış
        </div>
    </div>

    <!-- Orders Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($orders as $order)
            @php 
                $product = $order->product; 
                $isNew = session('new_order_id') == $order->id;
            @endphp
            <div class="glass-card p-6 rounded-2xl space-y-4 border-l-4 {{ $isNew ? 'border-blue-600 ring-2 ring-blue-500 ring-offset-2' : 'border-emerald-500' }} relative shadow-sm hover:border-slate-300 transition bg-white">
                <div class="flex items-start justify-between gap-3">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $order->order_status === 'ACTIVATED' || $order->order_status === 'INUSE' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                {{ $order->order_status }}
                            </span>
                            @if($isNew)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 text-blue-800 animate-pulse">YENİ SATIŞ</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-slate-900 leading-tight">{{ $product->display_name ?? ($product->product_name ?? 'eSIM Paketi') }}</h3>
                        <div class="text-[11px] text-slate-400 font-mono">Sipariş No: {{ $order->order_no ?? $order->channel_order_no }}</div>
                    </div>

                    <!-- 1-Click QR Modal Opener -->
                    <button onclick="showQrModal('{{ $order->qr_code }}', '{{ $order->iccid }}', '{{ $order->apple_install_url }}')" 
                        class="p-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-blue-200 text-center transition flex flex-col items-center gap-0.5 active:scale-95 shrink-0" title="QR Kodu Göster">
                        <i class="fa-solid fa-qrcode text-xl"></i>
                        <span class="text-[9px] font-extrabold uppercase tracking-wider">QR KOD</span>
                    </button>
                </div>

                <!-- Price & Quota Info -->
                <div class="flex items-center justify-between text-xs py-2 px-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-slate-400 text-[11px] block font-medium">Satış Tutarı</span>
                        <span class="text-sm font-black text-slate-900">€{{ number_format($order->sale_price, 2) }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 text-[11px] block font-medium">Tarih</span>
                        <span class="text-xs font-semibold text-slate-700">{{ $order->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                </div>

                <!-- ICCID & Branch Details Box -->
                <div class="bg-slate-50 p-3 rounded-xl space-y-1.5 border border-slate-200">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Satış Yapan Şube:</span>
                        <span class="px-2 py-0.5 rounded-md font-bold text-[11px] bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <i class="fa-solid fa-store text-[9px] mr-1"></i>{{ $order->branch_name ?? 'Merkez / Genel' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-200/60">
                        <span class="text-slate-500 font-medium">ICCID Numarası:</span>
                        <button onclick="copyToClipboard('{{ $order->iccid }}')" class="text-blue-600 hover:text-blue-800 font-bold text-[11px] flex items-center gap-1">
                            <i class="fa-solid fa-copy"></i> Kopyala
                        </button>
                    </div>
                    <div class="font-mono text-xs text-slate-900 font-extrabold tracking-wide break-all">
                        {{ $order->iccid ?? 'Atanıyor...' }}
                    </div>
                </div>

                <!-- iOS 1-Tap Install Link & Live Usage Check -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    @if($order->apple_install_url)
                        <a href="{{ $order->apple_install_url }}" target="_blank" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl text-center transition flex items-center justify-center gap-1.5 shadow-sm">
                            <i class="fa-brands fa-apple text-sm"></i>
                            <span>iOS Kurulum</span>
                        </a>
                    @endif

                    <button onclick="checkLiveUsage('{{ $order->id }}')" class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-xl border border-blue-200 text-center transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-chart-line text-xs"></i>
                        <span>Kota Sorgula</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-panel p-16 text-center text-slate-500 space-y-4 bg-white rounded-3xl border border-slate-200">
                <i class="fa-solid fa-box-open text-5xl text-slate-300"></i>
                <div class="space-y-1">
                    <p class="text-base font-bold text-slate-800">Henüz satışı yapılmış bir eSIM paketi bulunmuyor.</p>
                    <p class="text-xs text-slate-400">eSIM Satış Ekranı üzerinden ilk paketinizi satarak aktivasyonunu yapabilirsiniz.</p>
                </div>
                <a href="{{ route('customer.dashboard') }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl inline-flex items-center gap-2 shadow">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span>eSIM Satış Ekranına Git</span>
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="pt-4">
        {{ $orders->links() }}
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
            <button onclick="copyToClipboard(document.getElementById('qrTextValue').innerText)" class="w-full py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-copy"></i>
                <span>LPA Kodunu Kopyala</span>
            </button>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
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
                text: text,
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
