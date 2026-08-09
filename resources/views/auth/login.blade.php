@extends('layouts.app')

@section('title', 'Giriş Yap — TGT eSIM Panel')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center items-center">
    <div class="w-full max-w-md">
        <!-- Logo Branding -->
        <div class="text-center mb-8">
            <div class="bg-white/95 p-3 rounded-2xl shadow-2xl glow-blue mx-auto mb-4 inline-block">
                <img src="/images/logo.png" alt="POLO SIM" class="h-16 w-auto object-contain">
            </div>
            <h2 class="text-2xl font-bold text-white tracking-tight uppercase">POLO SIM Portalı</h2>
            <p class="text-xs text-amber-400 font-semibold uppercase tracking-widest mt-1">ONE SIM ONE WORLD</p>
        </div>

        <!-- Login Form Card -->
        <div class="glass-panel p-8 rounded-2xl shadow-2xl relative overflow-hidden">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl"></div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5 relative z-10">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">E-Posta Adresi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email', 'admin@tgt.com') }}" required 
                            class="w-full pl-10 pr-4 py-3 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200"
                            placeholder="eposta@adresiniz.com">
                    </div>
                    @error('email')
                        <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Şifre</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input id="password" type="password" name="password" value="password123" required 
                            class="w-full pl-10 pr-4 py-3 bg-slate-900/80 border border-slate-700/80 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition duration-200"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-blue-600 focus:ring-blue-500">
                        <span>Beni Hatırla</span>
                    </label>
                </div>

                <button type="submit" 
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg glow-blue transition duration-200 flex items-center justify-center gap-2">
                    <span>Giriş Yap</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="mt-8 pt-6 border-t border-slate-800/80 relative z-10 text-center">
                <p class="text-xs text-slate-400 font-medium mb-3">Hızlı Demo Girişi</p>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="fillLogin('admin@tgt.com')" 
                        class="px-3 py-2 bg-slate-800/80 hover:bg-slate-700 text-xs text-blue-400 rounded-xl border border-blue-500/20 font-medium transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin Girişi</span>
                    </button>
                    <button type="button" onclick="fillLogin('customer@tgt.com')" 
                        class="px-3 py-2 bg-slate-800/80 hover:bg-slate-700 text-xs text-purple-400 rounded-xl border border-purple-500/20 font-medium transition flex items-center justify-center gap-1.5">
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
