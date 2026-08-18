@extends('layouts.app')

@section('title', 'Profil & Şifre Ayarları — POLO SIM')

@section('content')
<div class="space-y-8 max-w-4xl">
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-user-gear text-blue-600"></i>
            <span>Hesap & Şifre Ayarları</span>
        </h1>
        <p class="text-slate-500 text-sm mt-1">Kişisel bilgilerinizi güncelleyin ve hesap şifrenizi güvenli bir şekilde değiştirin.</p>
    </div>

    <!-- Grid Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Form 1: Profile Information -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-id-card text-blue-600"></i>
                <span>Profil Bilgilerim</span>
            </h2>

            <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">E-Posta Adresi (Giriş)</label>
                    <input type="email" value="{{ $user->email }}" disabled class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 font-mono text-sm cursor-not-allowed">
                    <span class="text-[11px] text-slate-400 mt-1 block">E-posta adresi güvenlik nedeniyle değiştirilemez.</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ad Soyad</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-blue-600 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Firma / Kurum Adı</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" placeholder="Örn: POLO SIM Ltd." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Telefon Numarası</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+90 5XX XXX XX XX" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600 text-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition text-sm active:scale-95">
                        Profil Bilgilerini Kaydet
                    </button>
                </div>
            </form>
        </div>

        <!-- Form 2: Security & Password Change -->
        <div class="glass-panel p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2 pb-3 border-b border-slate-100">
                <i class="fa-solid fa-key text-amber-500"></i>
                <span>Şifre Değiştirme</span>
            </h2>

            <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mevcut Şifre</label>
                    <input type="password" name="current_password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-amber-500 text-sm">
                    @error('current_password')
                        <span class="text-xs text-rose-600 mt-1 font-bold block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Yeni Şifre</label>
                    <input type="password" name="new_password" required placeholder="En az 6 karakter" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-amber-500 text-sm">
                    @error('new_password')
                        <span class="text-xs text-rose-600 mt-1 font-bold block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Yeni Şifre Tekrarı</label>
                    <input type="password" name="new_password_confirmation" required placeholder="Yeni şifreyi tekrar yazın" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-amber-500 text-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md transition text-sm active:scale-95">
                        Şifremi Güncelle
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
