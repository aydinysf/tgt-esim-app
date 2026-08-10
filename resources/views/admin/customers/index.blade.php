@extends('layouts.app')

@section('title', 'Müşteri Yönetimi — POLO SIM')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-users text-blue-400"></i>
                <span>Müşteri Yönetimi</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Sisteme müşteri tanımlayın, atanan paketleri ve kâr marjlarını yönetin.</p>
        </div>
        <button onclick="document.getElementById('createCustomerModal').classList.remove('hidden')" 
            class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-sm rounded-xl shadow-lg glow-blue transition flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i>
            <span>Yeni Müşteri Ekle</span>
        </button>
    </div>

    <!-- Customer Table -->
    <div class="glass-panel p-6 rounded-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs uppercase bg-slate-900/60 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-semibold">Müşteri / Firma</th>
                        <th class="py-3.5 px-4 font-semibold">E-Posta & Tel</th>
                        <th class="py-3.5 px-4 font-semibold text-cyan-400">Mevcut Bakiye</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Atanmış Paket</th>
                        <th class="py-3.5 px-4 font-semibold text-center">Satın Alma</th>
                        <th class="py-3.5 px-4 font-semibold text-emerald-400">Toplam Kâr</th>
                        <th class="py-3.5 px-4 font-semibold text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="py-4 px-4">
                                <div class="font-semibold text-white">{{ $customer->name }}</div>
                                <div class="text-xs text-slate-400">{{ $customer->company_name ?? 'Bireysel Müşteri' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-mono text-xs text-slate-300">{{ $customer->email }}</div>
                                <div class="text-xs text-slate-500">{{ $customer->phone ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base font-extrabold text-cyan-300">₺{{ number_format($customer->balance, 2) }}</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    {{ $customer->customer_packages_count }} Paket
                                </span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                                    {{ $customer->orders_count }} Sipariş
                                </span>
                            </td>
                            <td class="py-4 px-4 font-extrabold text-emerald-400">
                                ₺{{ number_format($customer->orders_sum_profit ?? 0, 2) }}
                            </td>
                            <td class="py-4 px-4 text-right space-x-2">
                                <button onclick="openBalanceModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ $customer->balance }})" class="px-3 py-1.5 bg-cyan-600/20 hover:bg-cyan-600/30 text-cyan-300 text-xs font-semibold rounded-lg border border-cyan-500/30 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-coins"></i> Bakiye Yükle
                                </button>
                                <a href="{{ route('admin.packages.index') }}?customer_id={{ $customer->id }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-blue-400 text-xs font-semibold rounded-lg border border-blue-500/20 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-box"></i> Paket Ata
                                </a>
                                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="inline" onsubmit="return confirm('Bu müşteriyi ve tüm tanımlarını silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-semibold rounded-lg border border-rose-500/20 transition">
                                        <i class="fa-solid fa-trash-can"></i> Sil
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Henüz müşteri tanımlanmamış.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Customer -->
<div id="createCustomerModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="glass-panel max-w-lg w-full p-6 rounded-2xl shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-blue-400"></i>
                <span>Yeni Müşteri Tanımla</span>
            </h3>
            <button onclick="document.getElementById('createCustomerModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form action="{{ route('admin.customers.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Müşteri Ad Soyad</label>
                <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Firma / Kurum Adı</label>
                <input type="text" name="company_name" placeholder="Örn: ABC Turizm Ltd." class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">E-Posta Adresi (Giriş İçin)</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Telefon</label>
                    <input type="text" name="phone" placeholder="+90 5XX XXX XX XX" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Şifre</label>
                <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500" placeholder="••••••••">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('createCustomerModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-xl font-medium">İptal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl shadow-lg glow-blue">Müşteriyi Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add / Deduct Balance -->
<div id="balanceModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="glass-panel max-w-md w-full p-6 rounded-2xl shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-coins text-cyan-400"></i>
                <span>Müşteriye Bakiye Yükle / Düş</span>
            </h3>
            <button onclick="document.getElementById('balanceModal').classList.add('hidden')" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <form id="balanceForm" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase mb-1">Müşteri</label>
                <div id="balanceCustomerName" class="text-base font-bold text-white"></div>
                <div class="text-xs text-slate-400 mt-0.5">Mevcut Bakiye: <span id="balanceCurrentAmount" class="font-bold text-cyan-300"></span></div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Yüklenecek / Düşülecek Miktar (₺)</label>
                <input type="number" step="0.01" name="amount" required placeholder="Örn: 500 veya -100" class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-bold focus:outline-none focus:border-cyan-500">
                <span class="text-xs text-slate-500 mt-1 block">Bakiye eklemek için pozitif (Örn: 500), bakiye düşmek için eksi (Örn: -100) yazın.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="document.getElementById('balanceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-xl font-medium">İptal</button>
                <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-semibold rounded-xl shadow-lg glow-cyan">Bakiyeyi Güncelle</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openBalanceModal(customerId, customerName, currentBalance) {
        document.getElementById('balanceCustomerName').innerText = customerName;
        document.getElementById('balanceCurrentAmount').innerText = '₺' + parseFloat(currentBalance).toFixed(2);
        document.getElementById('balanceForm').action = '/admin/customers/' + customerId + '/balance';
        document.getElementById('balanceModal').classList.remove('hidden');
    }
</script>
@endsection
