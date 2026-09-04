@extends('layouts.app')

@section('title', 'Satış & Kâr Raporları — POLO SIM')

@section('content')
<div class="space-y-8">
    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i>
                <span>Satış & Kâr Analiz Raporu</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">Hangi bayiye hangi paketin kaç paraya satıldığı, şube kırılımları ve canlı eSIM profil/yükleme durumları.</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-2">
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="ICCID, Sipariş No, Bayi veya Paket..." 
                    class="w-full pl-9 pr-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-blue-600 shadow-sm">
            </div>

            <select name="customer_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 shadow-sm">
                <option value="">-- Tüm Bayiler --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->company_name ?? 'Bireysel' }})
                    </option>
                @endforeach
            </select>

            @if($selectedCustomerId && count($availableBranches) > 0)
                <select name="branch_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-blue-600 shadow-sm">
                    <option value="">-- Tüm Şubeler --</option>
                    @foreach($availableBranches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
                Filtrele
            </button>

            @if($selectedCustomerId || $selectedBranchId || !empty($search))
                <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs rounded-xl border border-slate-300 font-bold">Temizle</a>
            @endif
        </form>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase">Toplam Satış Cirosu</div>
            <div class="text-3xl font-black text-slate-900 mt-1">€{{ number_format($totalRevenue, 2) }}</div>
        </div>

        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase">Toplam Alış Maliyeti (TGT)</div>
            <div class="text-3xl font-black text-slate-700 mt-1">€{{ number_format($totalCost, 2) }}</div>
        </div>

        <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm border-l-4 border-emerald-500">
            <div class="text-xs font-bold text-emerald-700 uppercase">Toplam Net Kâr</div>
            <div class="text-3xl font-black text-emerald-600 mt-1">€{{ number_format($totalProfit, 2) }}</div>
        </div>
    </div>

    <!-- Package Profit Breakdown Table -->
    <div class="glass-panel p-6 rounded-2xl space-y-4 bg-white border border-slate-200 shadow-sm">
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-chart-column text-blue-600"></i>
            <span>Paket Bazlı Kâr Performansı</span>
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Paket Adı</th>
                        <th class="py-3.5 px-4 font-bold text-center">Satış Adedi</th>
                        <th class="py-3.5 px-4 font-bold">Toplam Ciro</th>
                        <th class="py-3.5 px-4 font-bold">Toplam Maliyet</th>
                        <th class="py-3.5 px-4 font-bold text-emerald-700">Toplam Kâr</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productStats as $stat)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $stat->product->product_name ?? 'Paket' }}
                                <div class="text-xs font-mono text-slate-400 font-medium">{{ $stat->product->product_code ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-black text-blue-700">{{ $stat->total_sales }} Adet</td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">€{{ number_format($stat->total_revenue, 2) }}</td>
                            <td class="py-3.5 px-4 text-slate-500 font-medium">€{{ number_format($stat->total_cost, 2) }}</td>
                            <td class="py-3.5 px-4 font-black text-emerald-600">+€{{ number_format($stat->total_profit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 font-medium">Kayıt bulunamadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Orders Log -->
    <div class="glass-panel p-6 rounded-2xl space-y-4 bg-white border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-receipt text-purple-600"></i>
                <span>Detaylı Sipariş, Şube & Canlı Yükleme Takibi</span>
            </h2>
            <span class="text-xs font-bold text-slate-500">Toplam {{ $orders->total() }} Sipariş</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Sipariş No</th>
                        <th class="py-3.5 px-4 font-bold">Bayi (Müşteri)</th>
                        <th class="py-3.5 px-4 font-bold">İşlem Şubesi</th>
                        <th class="py-3.5 px-4 font-bold">Paket Adı</th>
                        <th class="py-3.5 px-4 font-bold">ICCID</th>
                        <th class="py-3.5 px-4 font-bold">Maliyet / Fiyat</th>
                        <th class="py-3.5 px-4 font-bold text-emerald-700">Kâr</th>
                        <th class="py-3.5 px-4 font-bold">Tarih</th>
                        <th class="py-3.5 px-4 font-bold text-center">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-mono text-xs text-blue-700 font-bold whitespace-nowrap">
                                {{ $order->order_no ?? $order->channel_order_no }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                <div>{{ $order->customer->name ?? 'Silinmiş Müşteri' }}</div>
                                <span class="text-[10px] text-slate-400 block font-normal">{{ $order->customer->company_name ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md font-bold text-xs bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="fa-solid fa-store text-[10px] mr-1"></i>{{ $order->branch_name ?? 'Merkez / Genel' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-800 max-w-xs truncate">
                                {{ $order->product->product_name ?? 'Paket' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs text-slate-700 font-bold whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span>{{ $order->iccid ?? '-' }}</span>
                                    @if($order->iccid)
                                        <button onclick="copyToClipboard('{{ $order->iccid }}')" class="text-blue-600 hover:text-blue-800 text-[11px]" title="Kopyala">
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-xs whitespace-nowrap">
                                <div class="text-slate-500 font-medium">Alış: €{{ number_format($order->net_price, 2) }}</div>
                                <div class="font-extrabold text-slate-900">Satış: €{{ number_format($order->sale_price, 2) }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-black text-emerald-600 whitespace-nowrap">+€{{ number_format($order->profit, 2) }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-500 font-medium whitespace-nowrap">{{ $order->created_at->format('d.m.Y H:i') }}</td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap space-x-1">
                                <!-- QR Button -->
                                <button onclick="showQrModal('{{ $order->qr_code }}', '{{ $order->iccid }}', '{{ $order->apple_install_url }}')" 
                                    class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl border border-blue-200 text-center transition inline-flex items-center gap-1" title="QR Kodu Göster">
                                    <i class="fa-solid fa-qrcode"></i>
                                    <span class="text-[10px] font-bold">QR</span>
                                </button>

                                <!-- Live Check Button -->
                                <button onclick="checkAdminLiveStatus('{{ $order->id }}')" 
                                    class="p-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl border border-emerald-200 text-center transition inline-flex items-center gap-1" title="Canlı Yükleme & Kota Kontrolü">
                                    <i class="fa-solid fa-circle-nodes"></i>
                                    <span class="text-[10px] font-bold">Canlı Kontrol</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400 font-medium">Sipariş verisi bulunmuyor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Modal: QR Code Display -->
<div id="qrModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-sm w-full p-6 rounded-3xl shadow-2xl text-center space-y-4 relative border border-slate-200">
        <button onclick="document.getElementById('qrModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        
        <h3 class="text-lg font-bold text-slate-900 flex items-center justify-center gap-2">
            <i class="fa-solid fa-qrcode text-blue-600"></i>
            <span>eSIM QR Kodu</span>
        </h3>

        <div class="p-4 bg-white rounded-2xl mx-auto inline-block border border-slate-200 shadow-md">
            <div id="qrcodeDiv"></div>
        </div>

        <div class="text-xs text-slate-600 space-y-1">
            <p class="font-bold text-slate-800">LPA Aktivasyon Kodu:</p>
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

<!-- Modal: Live Diagnosis & Profile Status -->
<div id="statusModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-lg w-full p-6 md:p-7 rounded-3xl shadow-2xl space-y-4 relative border border-slate-200">
        <button onclick="document.getElementById('statusModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        
        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-satellite-dish text-emerald-600"></i>
            <span>TGT Canlı eSIM Durum & Yükleme Teşhisi</span>
        </h3>

        <div id="statusContent" class="space-y-4">
            <div class="text-center py-6 text-slate-500">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                <p>TGT SM-DP+ sunucularından anlık profil durumu ve kotalar sorgulanıyor...</p>
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

    function checkAdminLiveStatus(orderId) {
        document.getElementById('statusModal').classList.remove('hidden');
        const content = document.getElementById('statusContent');
        content.innerHTML = `
            <div class="text-center py-6 text-slate-500">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600 mb-2"></i>
                <p class="text-sm font-medium">TGT SM-DP+ sunucularından anlık profil durumu sorgulanıyor...</p>
            </div>
        `;

        fetch('/admin/orders/' + orderId + '/live-status')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const order = data.order || {};
                    const usage = data.usage || {};
                    const profile = data.profile || {};

                    const profileState = (profile.state || profile.profileState || 'Bilinmiyor').toUpperCase();
                    let stateBadgeClass = 'bg-slate-100 text-slate-800';
                    let stateExplain = 'Profil sunucuda hazır.';

                    if (profileState.includes('ENABLED') || profileState.includes('INUSE') || profileState.includes('INSTALLED') || profileState.includes('DOWNLOADED')) {
                        stateBadgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                        stateExplain = '✓ eSIM Telefona İndirilmiş / Yüklenmiş ve Aktif!';
                    } else if (profileState.includes('RELEASED') || profileState.includes('NOTACTIVE')) {
                        stateBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                        stateExplain = '⚠ QR Kod Henüz Telefona Taranmamış / Yüklenmemiş (Bekliyor).';
                    } else if (profileState.includes('DELETED')) {
                        stateBadgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                        stateExplain = '✕ Profil cihazdan silinmiş veya süresi dolmuş.';
                    }

                    content.innerHTML = `
                        <div class="space-y-4 text-xs">
                            <!-- Order Info -->
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-1.5">
                                <div class="flex justify-between"><span class="text-slate-400 font-medium">Sipariş No:</span><span class="font-bold text-slate-900 font-mono">${order.order_no}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400 font-medium">Bayi & Şube:</span><span class="font-bold text-slate-900">${order.customer} (${order.branch})</span></div>
                                <div class="flex justify-between"><span class="text-slate-400 font-medium">Paket:</span><span class="font-bold text-blue-700">${order.product}</span></div>
                                <div class="flex justify-between"><span class="text-slate-400 font-medium">ICCID:</span><span class="font-bold font-mono text-slate-900">${order.iccid || '-'}</span></div>
                            </div>

                            <!-- Live Profile State (Installed or Not) -->
                            <div class="p-4 rounded-2xl border ${stateBadgeClass} space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold uppercase tracking-wider text-[11px]">Telefona Yükleme Durumu:</span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-white shadow-sm">${profileState}</span>
                                </div>
                                <div class="font-bold text-xs mt-1">${stateExplain}</div>
                                <div class="text-[11px] opacity-80">Yükleme Sayısı: <strong>${profile.installCount || profile.downloadCount || '0'}</strong></div>
                            </div>

                            <!-- Live Data Usage -->
                            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                                <div class="font-bold text-slate-700 uppercase tracking-wider text-[10px]">Canlı Veri Kullanımı:</div>
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="p-2 bg-white rounded-xl border border-slate-200">
                                        <div class="text-[10px] text-slate-400">Toplam Kota</div>
                                        <div class="font-black text-slate-900 text-sm mt-0.5">${usage.dataTotal || '0'} MB</div>
                                    </div>
                                    <div class="p-2 bg-white rounded-xl border border-slate-200">
                                        <div class="text-[10px] text-slate-400">Harcanan</div>
                                        <div class="font-black text-rose-600 text-sm mt-0.5">${usage.dataUsage || '0'} MB</div>
                                    </div>
                                    <div class="p-2 bg-white rounded-xl border border-slate-200">
                                        <div class="text-[10px] text-slate-400">Kalan</div>
                                        <div class="font-black text-cyan-600 text-sm mt-0.5">${usage.dataResidual || '0'} MB</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<p class="text-rose-600 text-center py-4 font-bold">Durum sorgulanamadı.</p>';
                }
            })
            .catch(err => {
                content.innerHTML = '<p class="text-rose-600 text-center py-4 font-bold">Bağlantı hatası oluştu.</p>';
            });
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
</script>
@endpush
@endsection
