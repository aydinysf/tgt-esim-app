@extends('layouts.app')

@section('title', 'Şubelerim — POLO SIM')

@section('content')
<div class="space-y-6 max-w-4xl">
    <!-- Header & Add Action -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-store text-blue-600"></i>
                <span>Şube Yönetimi</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">İşletmenize ait şubeleri (Örn: Bakırköy, Üsküdar) tanımlayın. Satın alımları şube bazlı takip edin.</p>
        </div>
        <button onclick="document.getElementById('createBranchModal').classList.remove('hidden')" 
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
            <i class="fa-solid fa-plus"></i>
            <span>Yeni Şube Ekle</span>
        </button>
    </div>

    <!-- Branch List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($branches as $branch)
            <div class="glass-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-rose-500"></i>
                            <span>{{ $branch->name }}</span>
                        </div>
                        @if($branch->address)
                            <div class="text-xs text-slate-500 mt-1 font-medium">{{ $branch->address }}</div>
                        @endif
                        @if($branch->phone)
                            <div class="text-xs text-slate-400 font-mono mt-0.5"><i class="fa-solid fa-phone text-[10px] mr-1"></i>{{ $branch->phone }}</div>
                        @endif
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700 border border-blue-200">
                        {{ $branch->orders_count }} Sipariş
                    </span>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-xs">
                    <span class="text-slate-400 font-medium">Kayıt: {{ $branch->created_at->format('d.m.Y') }}</span>
                    <form action="{{ route('customer.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Bu şubeyi silmek istediğinize emin misiniz?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold transition">
                            <i class="fa-solid fa-trash-can"></i> Şubeyi Sil
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-panel p-8 rounded-2xl text-center text-slate-400 bg-white border border-slate-200">
                <i class="fa-solid fa-store text-4xl mb-3 text-slate-300"></i>
                <div class="text-base font-bold text-slate-700">Henüz tanımlı bir şubeniz bulunmuyor.</div>
                <div class="text-xs text-slate-500 mt-1">İşlemlerinizin nereden yapıldığını ayırmak için "Yeni Şube Ekle" butonunu kullanabilirsiniz.</div>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal: Create Branch -->
<div id="createBranchModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-md w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-store text-blue-600"></i>
                <span>Yeni Şube Tanımla</span>
            </h3>
            <button onclick="document.getElementById('createBranchModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('customer.branches.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Şube Adı (Zorunlu)</label>
                <input type="text" name="name" required placeholder="Örn: Bakırköy Şubesi" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Şube Adresi (İsteğe Bağlı)</label>
                <input type="text" name="address" placeholder="Örn: İncirli Cad. No:12" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Şube Telefonu (İsteğe Bağlı)</label>
                <input type="text" name="phone" placeholder="+90 212 XXX XX XX" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('createBranchModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-xl font-bold">İptal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md">Şubeyi Kaydet</button>
            </div>
        </form>
    </div>
</div>
@endsection
