@extends('layouts.app')

@section('title', 'Turlar & Deneyimler — Istanbul Welcome Card')

@section('content')
<div class="space-y-6">
    <!-- Top Greeting Header & Stripe Badge -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-landmark-dome text-amber-500"></i>
                <span>Turlar & Deneyimler</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">
                Istanbul Welcome Card turlarını, müze girişlerini ve ayrıcalıklı deneyimleri inceleyin; kredi kartı ile anında satın alın.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Stripe Credit Card Badge -->
            <div class="glass-panel px-4 py-2.5 rounded-2xl border-l-4 border-indigo-600 flex items-center gap-3 shadow-sm bg-white">
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base font-bold shrink-0 border border-indigo-200">
                    <i class="fa-brands fa-stripe text-xl"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Ödeme Yöntemi</span>
                    <div class="text-xs font-black text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-credit-card text-emerald-600"></i>
                        <span>Kredi Kartı & 3D Secure</span>
                    </div>
                </div>
            </div>

            <!-- Direct Link to Purchased Tours -->
            <a href="{{ route('customer.iwc.orders') }}" class="glass-card px-4 py-2.5 rounded-2xl text-xs font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 flex items-center gap-2 shadow-sm transition active:scale-95">
                <i class="fa-solid fa-ticket text-amber-600 text-sm"></i>
                <span>Satın Alınan Turlar ({{ $tourOrdersCount }})</span>
            </a>
        </div>
    </div>

    @if(session('info'))
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-info text-blue-600 text-base"></i>
            <span>{{ session('info') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs font-semibold flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-exclamation text-red-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Search & Filter Toolbar -->
    <div class="glass-card p-4 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input Form -->
        <form method="GET" action="{{ route('customer.iwc.index') }}" class="relative w-full md:w-96" data-no-loader>
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Tur veya deneyim adı ara..." 
                class="w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-amber-500 focus:bg-white transition">
            @if($sort !== 'default')
                <input type="hidden" name="sort" value="{{ $sort }}">
            @endif
        </form>

        <!-- Right Controls: Sort & Count -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <div class="text-xs text-slate-500 font-semibold">
                Toplam Tur: <span class="font-extrabold text-amber-600">{{ $products->total() }}</span>
            </div>

            <form method="GET" action="{{ route('customer.iwc.index') }}" data-no-loader>
                @if($search)
                    <input type="hidden" name="search" value="{{ $search }}">
                @endif
                <select name="sort" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-amber-500 transition">
                    <option value="default" {{ $sort === 'default' ? 'selected' : '' }}>Sıralama: Önerilen</option>
                    <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Fiyat: Düşükten Yükseğe</option>
                    <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Fiyat: Yüksekten Düşüğe</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($products as $product)
            @php
                $variants = $product->variants_data ?? [];
                $firstVariant = $variants[0] ?? null;
            @endphp
            <div class="glass-card rounded-2xl p-6 flex flex-col justify-between space-y-5 hover:border-amber-400 hover:shadow-md transition duration-200 relative group bg-white border border-slate-200">
                <div class="space-y-3.5">
                    <!-- Badges -->
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-compass text-[11px]"></i>
                            <span>{{ ucfirst($product->type ?: 'Deneyim') }}</span>
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[9px]"></i>
                            <span>Müsait</span>
                        </span>
                    </div>

                    <!-- Title -->
                    <h3 class="text-base font-extrabold text-slate-900 leading-snug group-hover:text-amber-600 transition">
                        {{ $product->getLocalizedName('tr') }}
                    </h3>

                    <!-- Short description -->
                    <div class="text-xs text-slate-500 line-clamp-3 leading-relaxed">
                        {!! strip_tags($product->getLocalizedDescription('tr') ?: 'Bu deneyim için detaylı bilgi ve rezervasyon seçeneklerini inceleyin.') !!}
                    </div>

                    <!-- Highlights / Variants info -->
                    @if(!empty($variants))
                        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-1.5">
                            @foreach(array_slice($variants, 0, 2) as $variant)
                                @php
                                    $vLabel = is_array($variant['label'] ?? null) ? ($variant['label']['tr'] ?? $variant['label']['en'] ?? reset($variant['label'])) : ($variant['label'] ?? 'Varyant');
                                @endphp
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-semibold text-slate-700 border border-slate-200 truncate max-w-[200px]">
                                    <i class="fa-solid fa-check text-[9px] text-emerald-600 mr-1"></i>{{ $vLabel }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Footer: Pricing & Action Button -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Başlangıç Fiyatı</span>
                        <div class="text-xl font-black text-slate-900 leading-tight">
                            €{{ number_format($product->price, 2) }}
                        </div>
                    </div>

                    <a href="{{ route('customer.iwc.show', $product->id) }}" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl transition flex items-center gap-2 shadow-sm active:scale-95">
                        <span>İncele & Satın Al</span>
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center glass-card rounded-2xl bg-white border border-slate-200">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mb-4 border border-slate-200">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800">Gösterilecek Ürün Bulunamadı</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    Arama kriterlerinize uygun tur bulunamadı veya henüz ürün senkronizasyonu yapılmamış.
                </p>
                <div class="mt-4">
                    <a href="{{ route('customer.iwc.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition inline-block">
                        Filtreleri Temizle
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection
