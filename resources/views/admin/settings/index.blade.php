@extends('layouts.app')

@section('title', 'TGT API Ayarları — TGT eSIM Panel')

@section('content')
<div class="space-y-8 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-sliders text-blue-400"></i>
            <span>TGT Global eSIM API Ayarları</span>
        </h1>
        <p class="text-slate-400 text-sm mt-1">TGT firması tarafınca sağlanan accountId, secret ve API ortam parametreleri.</p>
    </div>

    <!-- Live Balance Card -->
    <div class="glass-panel p-6 rounded-2xl border-l-4 border-cyan-500 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="text-xs font-semibold text-cyan-400 uppercase tracking-wider">TGT Canlı Kanal Bakiyesi</div>
            <div class="text-3xl font-extrabold text-white mt-1">
                ${{ number_format((float)($accountBalance['accountList'][0]['balance'] ?? 0), 2) }} {{ $accountBalance['currency'] ?? 'USD' }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Kanal Hesabı: <span class="font-mono text-cyan-300">{{ $accountBalance['accountId'] ?? $accountId }}</span></div>
        </div>
        <div class="px-4 py-2 bg-slate-900/80 rounded-xl border border-slate-700 text-xs text-slate-300">
            <div>Ödeme Yöntemi: <span class="font-bold text-white">{{ $accountBalance['settlementType'] ?? 'CASH' }}</span></div>
            <div>Durum: <span class="font-bold text-emerald-400">AKTİF</span></div>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="glass-panel p-6 rounded-2xl">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-2">Çalışma Ortamı (Environment)</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 bg-slate-900 border border-slate-700 rounded-xl cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="tgt_environment" value="sandbox" {{ $environment === 'sandbox' ? 'checked' : '' }} class="text-blue-600">
                        <div>
                            <div class="font-bold text-white text-sm">Sandbox (Test Ortamı)</div>
                            <div class="text-xs text-slate-400">Sanal paketler ve test siparişleri</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 bg-slate-900 border border-slate-700 rounded-xl cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="tgt_environment" value="production" {{ $environment === 'production' ? 'checked' : '' }} class="text-blue-600">
                        <div>
                            <div class="font-bold text-white text-sm">Production (Canlı Ortam)</div>
                            <div class="text-xs text-slate-400">Gerçek eSIM kartları ve bakiyeli işlem</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">TGT Base URL (HTTPS)</label>
                <input type="url" name="tgt_base_url" value="{{ old('tgt_base_url', $baseUrl) }}" required class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-blue-500">
                <span class="text-xs text-slate-500 mt-1 block">Test Sandbox: https://enterpriseapisandbox.tugegroup.com:8070/openapi</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Channel Account ID (accountId)</label>
                    <input type="text" name="tgt_account_id" value="{{ old('tgt_account_id', $accountId) }}" required class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">API Secret Key (secret)</label>
                    <input type="password" name="tgt_secret" value="{{ old('tgt_secret', $secret) }}" required class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono text-sm focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl shadow-lg glow-blue transition">
                    Ayarları Kaydet ve Token Sıfırla
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
