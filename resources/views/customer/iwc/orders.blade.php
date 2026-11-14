@extends('layouts.app')

@section('title', 'Satın Alınan Turlar — Istanbul Welcome Card')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-ticket text-amber-500"></i>
                <span>Satın Alınan Turlar & Rezervasyonlar</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Kredi kartı ile satın aldığınız IWC turlarını, bilet kodlarını ve etkinlik detaylarını bu ekrandan takip edebilirsiniz.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('customer.iwc.index') }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl transition flex items-center gap-2 shadow-sm active:scale-95">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Yeni Tur Satın Al</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Toolbar -->
    <div class="glass-card p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('customer.iwc.orders') }}" class="relative w-full md:w-96" data-no-loader>
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Sipariş no, tur adı veya katılımcı ara..." 
                class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white transition">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
        </form>

        <div class="flex items-center gap-2 w-full md:w-auto overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('customer.iwc.orders') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ empty($status) ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Tümü ({{ $orders->total() }})
            </a>
            <a href="{{ route('customer.iwc.orders', ['status' => 'paid']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'paid' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                <i class="fa-solid fa-check mr-1"></i> Ödendi
            </a>
            <a href="{{ route('customer.iwc.orders', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' }}">
                <i class="fa-solid fa-clock mr-1"></i> Beklemede
            </a>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="glass-panel rounded-2xl bg-white border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-extrabold text-[10px]">
                        <th class="p-4">Sipariş / Takip No</th>
                        <th class="p-4">Tur / Deneyim</th>
                        <th class="p-4">Etkinlik Tarihi & Saati</th>
                        <th class="p-4">Katılımcı</th>
                        <th class="p-4">Tutar & Ödeme</th>
                        <th class="p-4">IWC Rezervasyon Durumu</th>
                        <th class="p-4 text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4 font-mono font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $order->isPaid() ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    <span>{{ $order->order_no }}</span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-normal block mt-0.5">
                                    {{ $order->created_at->format('d.m.Y H:i') }}
                                </span>
                            </td>

                            <td class="p-4">
                                <div class="font-extrabold text-slate-900 leading-snug">{{ $order->product_name }}</div>
                                @if($order->variant_label)
                                    <span class="text-[10px] text-slate-500 font-semibold block mt-0.5">
                                        <i class="fa-solid fa-tag text-[9px] mr-1 text-amber-600"></i>{{ $order->variant_label }}
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 whitespace-nowrap">
                                <div class="font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-calendar-day text-[11px]"></i>
                                    <span>{{ $order->service_date }}</span>
                                </div>
                            </td>

                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $order->customer_first_name }} {{ $order->customer_last_name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $order->quantity }} Kişi • {{ $order->customer_phone }}</div>
                            </td>

                            <td class="p-4 whitespace-nowrap">
                                <div class="font-black text-slate-900 text-sm">€{{ number_format($order->total_amount, 2) }}</div>
                                @if($order->isPaid())
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded inline-flex items-center gap-1 mt-0.5">
                                        <i class="fa-brands fa-stripe text-indigo-600 text-xs"></i> Kredi Kartı (Ödendi)
                                    </span>
                                @else
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded inline-flex items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-clock text-amber-600"></i> Ödeme Bekliyor
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 whitespace-nowrap">
                                @if($order->iwc_order_id)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check text-blue-600"></i>
                                        <span>IWC #{{ $order->iwc_order_id }} ({{ ucfirst($order->iwc_order_status ?? 'Onaylı') }})</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200 inline-flex items-center gap-1">
                                        <i class="fa-solid fa-spinner fa-spin text-[10px]"></i>
                                        <span>İşlemde / İnceleniyor</span>
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-center whitespace-nowrap">
                                <button type="button" onclick="openVoucherModal({{ json_encode($order) }})"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] rounded-lg transition border border-slate-200 inline-flex items-center gap-1.5 active:scale-95">
                                    <i class="fa-solid fa-file-invoice text-amber-600"></i>
                                    <span>Voucher / Bilet</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl mb-3 border border-slate-200">
                                    <i class="fa-solid fa-ticket"></i>
                                </div>
                                <h4 class="font-bold text-slate-700 text-sm">Henüz Satın Alınmış Tur Bulunmuyor</h4>
                                <p class="text-xs text-slate-400 mt-1">Kredi kartı ile satın aldığınız turlar burada listelenecektir.</p>
                                <div class="mt-4">
                                    <a href="{{ route('customer.iwc.index') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition inline-block">
                                        Turları İncele
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Voucher Details Modal -->
<div id="voucherModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
    <div class="glass-card rounded-2xl max-w-lg w-full bg-white border border-slate-200 shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-amber-500 to-amber-600 text-white">
            <div class="flex items-center gap-2.5">
                <i class="fa-solid fa-ticket text-xl"></i>
                <div>
                    <h3 class="font-black text-sm tracking-wide">REZERVASYON VOUCHER</h3>
                    <span class="text-[10px] text-amber-100 uppercase tracking-widest block font-mono" id="modalOrderNo">IWC-XXXXXX</span>
                </div>
            </div>
            <button onclick="closeVoucherModal()" class="w-8 h-8 rounded-xl bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-4">
            <div class="text-center p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-1">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tur / Deneyim Adı</div>
                <h4 class="text-base font-extrabold text-slate-900" id="modalTourName">Tur Adı</h4>
                <div class="text-xs font-semibold text-amber-700" id="modalVariant">Standart Katılım</div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-0.5">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Tarih & Saat</span>
                    <strong class="text-slate-800" id="modalDate">01.01.2026 19:00</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-0.5">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Katılımcı Sayısı</span>
                    <strong class="text-slate-800" id="modalGuests">1 Kişi</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-0.5">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Müşteri</span>
                    <strong class="text-slate-800" id="modalCustomer">Ahmet Yılmaz</strong>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-0.5">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">IWC Bilet Kodu</span>
                    <strong class="text-blue-700 font-mono" id="modalIwcId">#146973</strong>
                </div>
            </div>

            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1" id="modalHotelBox">
                <span class="text-[10px] font-bold text-amber-700 uppercase block">Alınış / Otel Bilgisi:</span>
                <span id="modalHotelName" class="font-semibold">-</span>
            </div>

            <div class="text-center pt-2">
                <div id="voucherQrCode" class="flex justify-center mb-2"></div>
                <p class="text-[10px] text-slate-400">
                    Etkinlik başlangıç noktasında bu QR kodu veya IWC rezervasyon numaranızı ibraz edebilirsiniz.
                </p>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
            <button onclick="window.print()" class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition inline-flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-print"></i>
                <span>Yazdır / PDF</span>
            </button>
            <button onclick="closeVoucherModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition">
                Kapat
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openVoucherModal(order) {
        document.getElementById('modalOrderNo').innerText = order.order_no;
        document.getElementById('modalTourName').innerText = order.product_name;
        document.getElementById('modalVariant').innerText = order.variant_label || 'Standart Katılım';
        document.getElementById('modalDate').innerText = order.service_date;
        document.getElementById('modalGuests').innerText = order.quantity + ' Kişi';
        document.getElementById('modalCustomer').innerText = order.customer_first_name + ' ' + order.customer_last_name;
        document.getElementById('modalIwcId').innerText = order.iwc_order_id ? ('#' + order.iwc_order_id) : 'İnceleniyor';
        document.getElementById('modalHotelName').innerText = order.hotel_name || 'Belirtilmedi';

        // Render QR Code
        const qrContainer = document.getElementById('voucherQrCode');
        qrContainer.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            new QRCode(qrContainer, {
                text: 'IWC-ORDER:' + (order.iwc_order_id || order.order_no),
                width: 100,
                height: 100
            });
        }

        document.getElementById('voucherModal').classList.remove('hidden');
    }

    function closeVoucherModal() {
        document.getElementById('voucherModal').classList.add('hidden');
    }
</script>
@endpush
