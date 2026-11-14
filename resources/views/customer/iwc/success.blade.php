@extends('layouts.app')

@section('title', 'Ödeme Başarılı — Rezervasyon Onayı')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <div class="glass-card rounded-3xl p-8 bg-white border border-slate-200 shadow-xl text-center space-y-6">
        <!-- Success Icon -->
        <div class="w-20 h-20 mx-auto rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl border-2 border-emerald-200 shadow-sm animate-bounce">
            <i class="fa-solid fa-check"></i>
        </div>

        <div>
            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200 inline-block mb-2">
                Ödeme Başarılı • Stripe Korumalı
            </span>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">
                Rezervasyonunuz Onaylandı!
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-2 max-w-md mx-auto">
                Kredi kartı ödemeniz tahsil edilmiş olup, rezervasyon kaydınız Istanbul Welcome Card sistemine başarıyla iletilmiştir.
            </p>
        </div>

        <!-- Reservation Details Summary Card -->
        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-3.5 text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <span class="text-slate-500 font-semibold">Takip Referans No:</span>
                <span class="font-mono font-black text-slate-900">{{ $order->order_no }}</span>
            </div>

            @if($order->iwc_order_id)
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <span class="text-slate-500 font-semibold">IWC Rezervasyon No:</span>
                    <span class="font-mono font-black text-blue-700 bg-blue-50 px-2 py-0.5 rounded border border-blue-200">#{{ $order->iwc_order_id }}</span>
                </div>
            @endif

            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <span class="text-slate-500 font-semibold">Tur / Deneyim:</span>
                <span class="font-bold text-slate-800 text-right">{{ $order->product_name }}</span>
            </div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <span class="text-slate-500 font-semibold">Etkinlik Tarihi & Saati:</span>
                <span class="font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">{{ $order->service_date }}</span>
            </div>

            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <span class="text-slate-500 font-semibold">Katılımcı:</span>
                <span class="font-semibold text-slate-800">{{ $order->customer_first_name }} {{ $order->customer_last_name }} ({{ $order->quantity }} Kişi)</span>
            </div>

            <div class="flex items-center justify-between pt-1">
                <span class="text-sm font-extrabold text-slate-900">Tahsil Edilen Tutar:</span>
                <span class="text-xl font-black text-emerald-700">€{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
            <a href="{{ route('customer.iwc.orders') }}" class="w-full sm:w-auto px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs rounded-xl transition shadow-md flex items-center justify-center gap-2 active:scale-95">
                <i class="fa-solid fa-ticket text-xs"></i>
                <span>Satın Alınan Turlarıma Git</span>
            </a>
            <a href="{{ route('customer.iwc.index') }}" class="w-full sm:w-auto px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-compass text-xs"></i>
                <span>Yeni Turları İncele</span>
            </a>
        </div>
    </div>
</div>
@endsection
