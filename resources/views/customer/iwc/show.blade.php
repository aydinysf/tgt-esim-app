@extends('layouts.app')

@section('title', $product->getLocalizedName('tr') . ' — IWC Rezervasyon')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('customer.iwc.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-900 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Tüm Turlara Dön</span>
        </a>

        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                <i class="fa-solid fa-bolt text-[10px] mr-1"></i> Anında Onay
            </span>
            <span class="px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                <i class="fa-brands fa-stripe mr-1"></i> Stripe Korumalı
            </span>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Left Column: Product Details & Content Tabs (8 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Main Hero Card -->
            <div class="glass-card rounded-2xl p-6 md:p-8 bg-white border border-slate-200 space-y-5">
                <div class="flex items-center gap-2.5">
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                        <i class="fa-solid fa-landmark text-[10px]"></i>
                        <span>{{ ucfirst($product->type ?: 'Tur & Deneyim') }}</span>
                    </span>
                    <span class="text-xs font-mono text-slate-400">Ürün ID: #{{ $product->id }}</span>
                </div>

                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                    {{ $product->getLocalizedName('tr') }}
                </h1>

                <!-- Short summary description -->
                <div class="text-sm text-slate-600 leading-relaxed border-l-2 border-amber-500 pl-4 py-1">
                    {!! $product->getLocalizedDescription('tr') !!}
                </div>
            </div>

            <!-- Contents & Inclusions (Accordion / Content blocks) -->
            @php $contents = $product->contents_data ?? []; @endphp
            @if(!empty($contents))
                <div class="space-y-4">
                    @foreach($contents as $idx => $contentItem)
                        @php
                            $title = is_array($contentItem['title'] ?? null) ? ($contentItem['title']['tr'] ?? $contentItem['title']['en'] ?? reset($contentItem['title'])) : ($contentItem['title'] ?? 'Bilgi');
                            $text = is_array($contentItem['text'] ?? null) ? ($contentItem['text']['tr'] ?? $contentItem['text']['en'] ?? reset($contentItem['text'])) : ($contentItem['text'] ?? '');
                        @endphp
                        <div class="glass-card rounded-2xl p-6 bg-white border border-slate-200 space-y-3">
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                                @if(stripos($title, 'cancellation') !== false || stripos($title, 'iptal') !== false)
                                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                                @elseif(stripos($title, 'menu') !== false)
                                    <i class="fa-solid fa-utensils text-amber-600"></i>
                                @else
                                    <i class="fa-solid fa-circle-info text-blue-600"></i>
                                @endif
                                <span>{{ $title }}</span>
                            </h3>
                            <div class="prose prose-sm text-xs text-slate-600 leading-relaxed max-w-none">
                                {!! $text !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right Column: Interactive Booking & Stripe Checkout Box (5 cols) -->
        <div class="lg:col-span-5 sticky top-24">
            <div class="glass-panel rounded-2xl p-6 md:p-7 bg-white border border-slate-200 shadow-lg space-y-6">
                <!-- Header Pricing -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Kişi Başı</span>
                        <div class="text-2xl font-black text-slate-900" id="displayUnitPrice">
                            €{{ number_format($product->price, 2) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 inline-flex items-center gap-1">
                            <i class="fa-solid fa-credit-card"></i> Kredi Kartı (Stripe)
                        </span>
                    </div>
                </div>

                <!-- Booking Form -->
                <form action="{{ route('customer.iwc.checkout') }}" method="POST" id="bookingForm" class="space-y-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="service_time" id="selectedServiceTime" value="19:00">

                    <!-- Step 1: Select Variant / Package Type -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">
                            1. Paket / Menü Seçimi <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            @forelse($variants as $v)
                                @php
                                    $vLabel = is_array($v['label'] ?? null) ? ($v['label']['tr'] ?? $v['label']['en'] ?? reset($v['label'])) : ($v['label'] ?? 'Standart Katılım');
                                    $vPrice = (float) ($v['price']['amount'] ?? $product->price);
                                @endphp
                                <label class="flex items-center justify-between p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-amber-400 transition bg-slate-50 has-[:checked]:bg-amber-50/50 has-[:checked]:border-amber-500">
                                    <div class="flex items-center gap-3">
                                        <input type="radio" name="product_variant_id" value="{{ $v['id'] }}" data-price="{{ $vPrice }}"
                                            class="variant-radio text-amber-600 focus:ring-amber-500" {{ $loop->first ? 'checked' : '' }} onchange="calculateTotal()">
                                        <div class="text-xs font-bold text-slate-800">{{ $vLabel }}</div>
                                    </div>
                                    <span class="text-xs font-extrabold text-slate-900">€{{ number_format($vPrice, 2) }}</span>
                                </label>
                            @empty
                                <input type="hidden" name="product_variant_id" value="0" data-price="{{ $product->price }}">
                                <div class="text-xs text-slate-500 italic">Standart Rezervasyon</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Step 2: Select Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            2. Rezervasyon Tarihi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" name="service_date" id="serviceDateInput" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white transition"
                                onchange="fetchSlotsForDate(this.value)" required>
                        </div>
                    </div>

                    <!-- Step 3: Available Time Slots -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>3. Saat Slotu <span class="text-red-500">*</span></span>
                            <span id="slotLoadingText" class="text-[10px] text-amber-600 font-semibold hidden">
                                <i class="fa-solid fa-spinner fa-spin mr-1"></i> Slotlar güncelleniyor...
                            </span>
                        </label>
                        <div id="slotsContainer" class="grid grid-cols-3 gap-2">
                            <!-- Dynamic or default buttons -->
                            <button type="button" class="slot-btn px-2.5 py-2 rounded-xl border border-amber-500 bg-amber-50 text-amber-800 font-bold text-xs text-center transition" onclick="selectSlot(this, '19:00')">
                                19:00
                            </button>
                            <button type="button" class="slot-btn px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs text-center hover:border-amber-400 transition" onclick="selectSlot(this, '19:30')">
                                19:30
                            </button>
                            <button type="button" class="slot-btn px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs text-center hover:border-amber-400 transition" onclick="selectSlot(this, '20:00')">
                                20:00
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Number of Guests (Quantity) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            4. Kişi Sayısı (Katılımcı) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="adjustQty(-1)" class="w-10 h-10 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold hover:bg-slate-100 transition active:scale-95 flex items-center justify-center">
                                <i class="fa-solid fa-minus text-xs"></i>
                            </button>
                            <input type="number" name="quantity" id="quantityInput" value="1" min="1" max="50"
                                class="w-20 text-center py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-extrabold text-slate-900 focus:outline-none focus:border-amber-500"
                                onchange="calculateTotal()" readonly>
                            <button type="button" onclick="adjustQty(1)" class="w-10 h-10 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold hover:bg-slate-100 transition active:scale-95 flex items-center justify-center">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 5: Contact Information -->
                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <div class="text-xs font-bold text-slate-700">5. Katılımcı Bilgileri</div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <input type="text" name="customer_first_name" placeholder="Adınız *" value="{{ Auth::user()->name }}" required
                                class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500">
                            <input type="text" name="customer_last_name" placeholder="Soyadınız *" required
                                class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500">
                        </div>
                        <input type="email" name="customer_email" placeholder="E-posta Adresi *" value="{{ Auth::user()->email }}" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500">
                        <input type="tel" name="customer_phone" placeholder="Telefon Numarası (Örn: +905321234567) *" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500">
                        <input type="text" name="hotel_name" placeholder="Alınacağınız Otel / Konum (Opsiyonel)"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500">
                    </div>

                    <!-- Total Amount Summary Box -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span>Birim Fiyat:</span>
                            <span id="summaryUnitPrice" class="font-bold">€{{ number_format($product->price, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span>Katılımcı Adedi:</span>
                            <span id="summaryQuantity" class="font-bold">1 Kişi</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                            <span class="text-sm font-extrabold text-slate-900">Toplam Ödenecek:</span>
                            <span class="text-xl font-black text-emerald-700" id="summaryTotalAmount">€{{ number_format($product->price, 2) }}</span>
                        </div>
                    </div>

                    <!-- Submit CTA -->
                    <button type="submit" id="submitBtn" class="w-full py-3.5 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-extrabold text-sm rounded-xl transition duration-200 shadow-md flex items-center justify-center gap-2 active:scale-98">
                        <i class="fa-solid fa-lock text-xs"></i>
                        <span>Kredi Kartı ile Satın Al (Stripe)</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>

                    <!-- Trust badges -->
                    <div class="text-center pt-2">
                        <div class="flex items-center justify-center gap-3 text-slate-400 text-lg mb-1">
                            <i class="fa-brands fa-cc-visa"></i>
                            <i class="fa-brands fa-cc-mastercard"></i>
                            <i class="fa-brands fa-stripe"></i>
                        </div>
                        <p class="text-[10px] text-slate-400">
                            256-bit SSL Güvenli Ödeme. Bakiye hesabınızdan düşülmez, kartınızdan tahsil edilir.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentUnitPrice = {{ (float) $product->price }};

    function adjustQty(amount) {
        const input = document.getElementById('quantityInput');
        let current = parseInt(input.value) || 1;
        current += amount;
        if (current < 1) current = 1;
        if (current > 50) current = 50;
        input.value = current;
        calculateTotal();
    }

    function calculateTotal() {
        const checkedRadio = document.querySelector('.variant-radio:checked');
        if (checkedRadio) {
            currentUnitPrice = parseFloat(checkedRadio.getAttribute('data-price')) || currentUnitPrice;
        }

        const qty = parseInt(document.getElementById('quantityInput').value) || 1;
        const total = (currentUnitPrice * qty).toFixed(2);

        document.getElementById('displayUnitPrice').innerText = '€' + currentUnitPrice.toFixed(2);
        document.getElementById('summaryUnitPrice').innerText = '€' + currentUnitPrice.toFixed(2);
        document.getElementById('summaryQuantity').innerText = qty + ' Kişi';
        document.getElementById('summaryTotalAmount').innerText = '€' + total;
    }

    function selectSlot(btn, time) {
        document.getElementById('selectedServiceTime').value = time;
        document.querySelectorAll('.slot-btn').forEach(b => {
            b.className = 'slot-btn px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs text-center hover:border-amber-400 transition';
        });
        btn.className = 'slot-btn px-2.5 py-2 rounded-xl border border-amber-500 bg-amber-50 text-amber-800 font-bold text-xs text-center transition';
    }

    function fetchSlotsForDate(date) {
        const loading = document.getElementById('slotLoadingText');
        const container = document.getElementById('slotsContainer');
        loading.classList.remove('hidden');

        fetch(`{{ route('customer.iwc.availability', $product->id) }}?date=${date}`)
            .then(res => res.json())
            .then(data => {
                loading.classList.add('hidden');
                if (data.status && data.slots && data.slots.length > 0) {
                    container.innerHTML = '';
                    data.slots.forEach((slot, index) => {
                        const time = slot.start_time;
                        const isFirst = index === 0;
                        if (isFirst) {
                            document.getElementById('selectedServiceTime').value = time;
                        }
                        const isFull = slot.status === 'full' || slot.available === 0;
                        const disabledAttr = isFull ? 'disabled' : '';
                        const btnClass = isFull 
                            ? 'slot-btn opacity-40 cursor-not-allowed px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-100 text-slate-400 font-semibold text-xs text-center'
                            : (isFirst 
                                ? 'slot-btn px-2.5 py-2 rounded-xl border border-amber-500 bg-amber-50 text-amber-800 font-bold text-xs text-center transition'
                                : 'slot-btn px-2.5 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs text-center hover:border-amber-400 transition');

                        container.innerHTML += `
                            <button type="button" class="${btnClass}" ${disabledAttr} onclick="selectSlot(this, '${time}')">
                                ${time} ${isFull ? '<span class=\"text-[9px] block text-red-500\">Dolu</span>' : ''}
                            </button>
                        `;
                    });
                }
            })
            .catch(() => {
                loading.classList.add('hidden');
            });
    }

    // Initial calculation on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateTotal();
    });
</script>
@endpush
