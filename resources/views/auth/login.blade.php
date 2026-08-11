@extends('layouts.app')

@section('title', 'Giriş Yap — POLO SIM')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center items-center">
    <div class="w-full max-w-md">
        <!-- Logo Branding -->
        <div class="text-center mb-8">
            <div class="bg-white p-3.5 rounded-2xl shadow-md border border-slate-200 mx-auto mb-4 inline-block">
                <img src="/images/logo.png" alt="POLO SIM" class="h-16 w-auto object-contain">
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight uppercase">POLO SIM Portalı</h2>
            <p class="text-xs text-amber-600 font-extrabold uppercase tracking-widest mt-1">ONE SIM ONE WORLD</p>
        </div>

        <!-- Login Form Card -->
        <div class="glass-panel p-8 rounded-3xl shadow-xl relative overflow-hidden bg-white border border-slate-200">
            <form method="POST" action="{{ route('login') }}" class="space-y-5 relative z-10">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">E-Posta Adresi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email', 'admin@polosim.com') }}" required 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition duration-200"
                            placeholder="eposta@adresiniz.com">
                    </div>
                    @error('email')
                        <p class="text-rose-600 text-xs mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Şifre</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input id="password" type="password" name="password" value="password123" required 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition duration-200"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600">
                    <label class="flex items-center gap-2 cursor-pointer font-medium">
                        <input type="checkbox" name="remember" class="rounded bg-slate-100 border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>Beni Hatırla</span>
                    </label>
                </div>

                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition duration-200 flex items-center justify-center gap-2 active:scale-95">
                    <span>Giriş Yap</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="mt-8 pt-6 border-t border-slate-100 relative z-10 text-center">
                <p class="text-xs text-slate-500 font-semibold mb-3">Hızlı Demo Girişi</p>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="fillLogin('admin@polosim.com')" 
                        class="px-3 py-2 bg-blue-50 hover:bg-blue-100 text-xs text-blue-700 rounded-xl border border-blue-200 font-bold transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin Girişi</span>
                    </button>
                    <button type="button" onclick="fillLogin('customer@polosim.com')" 
                        class="px-3 py-2 bg-purple-50 hover:bg-purple-100 text-xs text-purple-700 rounded-xl border border-purple-200 font-bold transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-user-tag"></i>
                        <span>Müşteri Girişi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function fillLogin(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password123';
    }
</script>
@endsection
