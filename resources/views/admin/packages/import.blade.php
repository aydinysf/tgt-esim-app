@extends('layouts.app')

@section('title', 'Excel / CSV Fiyat Listesi Yükleme — POLO SIM')

@section('content')
<div class="space-y-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                <span>TGT Fiyat Listesi İçe Aktarma (Excel / CSV)</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">TGT toptancısından gelen güncel fiyat listelerini doğrudan veritabanına aktarın.</p>
        </div>

        <a href="{{ route('admin.packages.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl border border-slate-300 transition flex items-center gap-2 active:scale-95 self-start">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Paket Listesine Dön</span>
        </a>
    </div>

    <!-- Alert Explanation Box -->
    <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm flex gap-3 shadow-sm">
        <i class="fa-solid fa-circle-exclamation text-amber-600 text-xl shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <h3 class="font-bold text-base text-amber-950">Neden API Yerine Excel / CSV Yüklüyoruz?</h3>
            <p class="text-xs text-amber-800 leading-relaxed">
                TGT firması, toptan müşterilerine özel indirimli fiyatları ve tüm dünya paketlerini <strong>Excel Quote Sheet</strong> olarak gönderir. TGT'nin API listesi test hesaplarında kısıtlı veya 0 adet dönebilmektedir; ancak sipariş anında TGT doğrudan bu Excel'deki <strong>API Product Code</strong> kodlarını kabul eder. Buradan yükleme yaparak resmi toptan alış fiyatlarını sisteme eksiksiz tanımlamış olursunuz.
            </p>
        </div>
    </div>

    <!-- Section 1: Server Directory Batch Import -->
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-emerald-600 space-y-5 bg-white border border-slate-200 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-folder-open text-emerald-600"></i>
                    <span>Yöntem 1: Sunucudaki `fiyat_listesi` Klasöründen Otomatik Yükle</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Sunucuda hazır bulunan tüm sayfa CSV dosyalarını tek tıkla tarar ve veritabanına kaydeder.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                {{ count($availableFiles ?? []) }} Adet CSV Dosyası Hazır
            </span>
        </div>

        @if(!empty($availableFiles))
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider block mb-2">Klasörde Tespit Edilen Dosyalar:</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto">
                    @foreach($availableFiles as $f)
                        <div class="flex items-center gap-2 p-2 bg-white rounded-lg border border-slate-200 text-xs text-slate-700">
                            <i class="fa-solid fa-file-csv text-emerald-600"></i>
                            <span class="truncate font-medium" title="{{ $f }}">{{ $f }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('admin.packages.import-price-list') }}" method="POST" onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i> <span>Yükleniyor (Lütfen bekleyin, 10-20 sn sürebilir)...</span>';">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center justify-center gap-2 active:scale-95">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Tüm Dosyaları İçe Aktar (Güvenli Güncelleme)</span>
                </button>
            </form>
        @else
            <div class="p-4 bg-slate-50 rounded-xl text-center text-slate-500 text-sm">
                `fiyat_listesi` klasöründe taranacak CSV dosyası bulunamadı. Aşağıdaki alandan dosya yükleyebilirsiniz.
            </div>
        @endif
    </div>

    <!-- Section 2: Direct File Upload from Browser -->
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-blue-600 space-y-5 bg-white border border-slate-200 shadow-sm">
        <div>
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-cloud-arrow-up text-blue-600"></i>
                <span>Yöntem 2: Bilgisayarınızdan CSV / TXT Yükleyin</span>
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Bilgisayarınızdaki herhangi bir `.csv` dosyasını seçip doğrudan sisteme yükleyebilirsiniz.</p>
        </div>

        <form action="{{ route('admin.packages.import-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = '<i class=\'fa-solid fa-spinner fa-spin\'></i> <span>Yükleniyor...</span>';">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-2">CSV Dosyası Seçin</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required
                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-xl bg-slate-50 p-2">
                <span class="text-[11px] text-slate-400 mt-1 block">TGT'nin herhangi bir sayfa CSV dosyasını (`__ep1.csv`, `__Global.csv` vb.) seçebilirsiniz.</span>
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
                <i class="fa-solid fa-upload"></i>
                <span>Seçilen Dosyayı Yükle ve Aktar</span>
            </button>
        </form>
    </div>
</div>
@endsection
