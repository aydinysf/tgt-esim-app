@extends('layouts.app')

@section('title', 'Müşteri Yönetimi — POLO SIM')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-users text-blue-600"></i>
                <span>Müşteri Yönetimi</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">Sisteme müşteri tanımlayın, bakiye ekleyin ve atanan paketleri yönetin.</p>
        </div>
        <button onclick="document.getElementById('createCustomerModal').classList.remove('hidden')" 
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
            <i class="fa-solid fa-user-plus"></i>
            <span>Yeni Müşteri Ekle</span>
        </button>
    </div>

    <!-- Customer Table -->
    <div class="glass-panel p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="text-xs uppercase bg-slate-50 text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Müşteri / Firma</th>
                        <th class="py-3.5 px-4 font-bold">E-Posta & Tel</th>
                        <th class="py-3.5 px-4 font-bold text-cyan-700">Mevcut Bakiye</th>
                        <th class="py-3.5 px-4 font-bold text-center">Şubeler</th>
                        <th class="py-3.5 px-4 font-bold text-center">Atanmış Paket</th>
                        <th class="py-3.5 px-4 font-bold text-center">Satın Alma</th>
                        <th class="py-3.5 px-4 font-bold text-emerald-700">Toplam Kâr</th>
                        <th class="py-3.5 px-4 font-bold text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900">{{ $customer->name }}</div>
                                <div class="text-xs text-slate-500 font-medium">{{ $customer->company_name ?? 'Bireysel Müşteri' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-mono text-xs text-slate-800 font-semibold">{{ $customer->email }}</div>
                                <div class="text-xs text-slate-500">{{ $customer->phone ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <span class="text-base font-black text-cyan-700">€{{ number_format($customer->balance, 2) }}</span>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <button onclick="openBranchModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ json_encode($customer->branches) }})" class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition">
                                    <i class="fa-solid fa-store text-[10px] mr-1"></i>{{ $customer->branches_count }} Şube
                                </button>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <button onclick="openCustomerPackagesModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ json_encode($customer->customerPackages) }})" class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition" title="Atanan Paketleri Gör / Yönet">
                                    <i class="fa-solid fa-box text-[10px] mr-1"></i>{{ $customer->customer_packages_count }} Paket
                                </button>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ $customer->orders_count }} Sipariş
                                </span>
                            </td>
                            <td class="py-4 px-4 font-black text-emerald-600">
                                €{{ number_format($customer->orders_sum_profit ?? 0, 2) }}
                            </td>
                            <td class="py-4 px-4 text-right space-x-2">
                                <button onclick="openCustomerPackagesModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ json_encode($customer->customerPackages) }})" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg border border-blue-200 transition inline-flex items-center gap-1" title="Atanan Paketleri Gör / Yönet">
                                    <i class="fa-solid fa-list-check"></i> Paketleri Gör
                                </button>
                                <button onclick="openPasswordModal({{ $customer->id }}, '{{ addslashes($customer->name) }}')" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold rounded-lg border border-amber-200 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-key"></i> Şifre
                                </button>
                                <button onclick="openBranchModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ json_encode($customer->branches) }})" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-store"></i> Şubeler
                                </button>
                                <button onclick="openBalanceModal({{ $customer->id }}, '{{ addslashes($customer->name) }}', {{ $customer->balance }})" class="px-3 py-1.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 text-xs font-bold rounded-lg border border-cyan-200 transition inline-flex items-center gap-1 active:scale-95">
                                    <i class="fa-solid fa-coins"></i> Bakiye
                                </button>
                                <a href="{{ route('admin.packages.index') }}?customer_id={{ $customer->id }}" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200 transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-plus"></i> Paket Ata
                                </a>
                                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" class="inline" onsubmit="return confirm('Bu müşteriyi ve tüm tanımlarını silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold rounded-lg border border-rose-200 transition">
                                        <i class="fa-solid fa-trash-can"></i> Sil
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 font-medium">Henüz müşteri tanımlanmamış.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Create Customer -->
<div id="createCustomerModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-lg w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-plus text-blue-600"></i>
                <span>Yeni Müşteri Tanımla</span>
            </h3>
            <button onclick="document.getElementById('createCustomerModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('admin.customers.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Müşteri Ad Soyad</label>
                <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Firma / Kurum Adı</label>
                <input type="text" name="company_name" placeholder="Örn: ABC Turizm Ltd." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">E-Posta Adresi (Giriş İçin)</label>
                    <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Telefon</label>
                    <input type="text" name="phone" placeholder="+90 5XX XXX XX XX" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Şifre</label>
                <input type="password" name="password" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600" placeholder="••••••••">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('createCustomerModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-xl font-bold">İptal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md">Müşteriyi Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add / Deduct Balance -->
<div id="balanceModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-md w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-coins text-cyan-600"></i>
                <span>Müşteriye Bakiye Yükle / Düş</span>
            </h3>
            <button onclick="document.getElementById('balanceModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form id="balanceForm" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Müşteri</label>
                <div id="balanceCustomerName" class="text-base font-extrabold text-slate-900"></div>
                <div class="text-xs text-slate-500 mt-0.5 font-medium">Mevcut Bakiye: <span id="balanceCurrentAmount" class="font-extrabold text-cyan-700"></span></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Yüklenecek / Düşülecek Miktar (€)</label>
                <input type="number" step="0.01" name="amount" required placeholder="Örn: 500 veya -100" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-extrabold focus:outline-none focus:border-cyan-600">
                <span class="text-xs text-slate-500 mt-1 block font-medium">Bakiye eklemek için pozitif (Örn: 500), bakiye düşmek için eksi (Örn: -100) yazın.</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('balanceModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-xl font-bold">İptal</button>
                <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-bold rounded-xl shadow-md">Bakiyeyi Güncelle</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Admin Branch Management -->
<div id="adminBranchModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-lg w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200 space-y-4">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-store text-indigo-600"></i>
                    <span>Müşteri Şubeleri / Bayileri</span>
                </h3>
                <div id="branchModalCustomerName" class="text-xs text-slate-500 font-medium"></div>
            </div>
            <button onclick="document.getElementById('adminBranchModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <!-- Add Branch Form -->
        <form id="adminAddBranchForm" method="POST" class="space-y-3 p-4 bg-slate-50 rounded-2xl border border-slate-200">
            @csrf
            <div class="text-xs font-bold text-slate-700 uppercase">Müşteriye Yeni Şube Ekle</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <input type="text" name="name" required placeholder="Şube Adı (Örn: Bakırköy)" class="px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:border-indigo-600">
                <input type="text" name="phone" placeholder="Telefon (İsteğe Bağlı)" class="px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:border-indigo-600">
            </div>
            <input type="text" name="address" placeholder="Adres (İsteğe Bağlı)" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:border-indigo-600">
            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow transition">Şubeyi Kaydet</button>
        </form>

        <!-- Existing Branch List -->
        <div class="space-y-2 max-h-60 overflow-y-auto">
            <div class="text-xs font-bold text-slate-400 uppercase">Tanımlı Şubeler</div>
            <div id="branchListContainer" class="space-y-2"></div>
        </div>
    </div>
</div>

<!-- Modal: Admin Reset Customer Password -->
<div id="passwordModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-md w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-key text-amber-500"></i>
                <span>Müşteri Şifresini Değiştir</span>
            </h3>
            <button onclick="document.getElementById('passwordModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form id="passwordForm" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Müşteri</label>
                <div id="passwordCustomerName" class="text-base font-extrabold text-slate-900"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Yeni Şifre (En Az 6 Karakter)</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-extrabold focus:outline-none focus:border-amber-500">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('passwordModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-xl font-bold">İptal</button>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-md">Yeni Şifreyi Kaydet</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View & Manage Customer Assigned Packages -->
<div id="customerPackagesModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-3xl w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200 space-y-4 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
            <div>
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-blue-600"></i>
                    <span>Müşteriye Atanmış Paketler</span>
                </h3>
                <div class="text-xs text-slate-500 font-medium mt-0.5">
                    Müşteri: <span id="pkgModalCustomerName" class="font-extrabold text-slate-900"></span> &nbsp;|&nbsp; 
                    Toplam: <span id="pkgModalCount" class="font-bold text-blue-700"></span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a id="pkgModalAddLink" href="#" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> Yeni Paket Ata
                </a>
                <button onclick="document.getElementById('customerPackagesModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold p-1">&times;</button>
            </div>
        </div>

        <!-- Search in assigned packages -->
        <div class="shrink-0">
            <input type="text" id="pkgModalSearchInput" onkeyup="filterPkgModalTable()" placeholder="🔍 Atanmış paketlerde ara..."
                class="w-full px-3.5 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-blue-600">
        </div>

        <!-- Assigned Packages Table -->
        <div class="overflow-y-auto flex-1 border border-slate-100 rounded-2xl">
            <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 uppercase sticky top-0 border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3 font-bold">Paket Adı</th>
                        <th class="py-2.5 px-3 font-bold">Veri</th>
                        <th class="py-2.5 px-3 font-bold text-right">Alış Maliyeti (€)</th>
                        <th class="py-2.5 px-3 font-bold text-right text-slate-900">Satış Fiyatı (€)</th>
                        <th class="py-2.5 px-3 font-bold text-right text-emerald-600">Birim Kâr (€)</th>
                        <th class="py-2.5 px-3 font-bold text-center">İşlem</th>
                    </tr>
                </thead>
                <tbody id="pkgModalTableBody" class="divide-y divide-slate-100">
                    <!-- Dynamic Rows -->
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs text-slate-500 shrink-0">
            <span class="text-[11px] font-medium">Paketi kaldırdığınızda müşterinin ekranından anında silinir.</span>
            <button type="button" onclick="document.getElementById('customerPackagesModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Kapat</button>
        </div>
    </div>
</div>

<script>
    const USD_TO_EUR_RATE = {{ (float) ($usdToEurRate ?? 0.92) }};

    function openCustomerPackagesModal(customerId, customerName, packages) {
        document.getElementById('pkgModalCustomerName').innerText = customerName;
        document.getElementById('pkgModalCount').innerText = (packages ? packages.length : 0) + ' Paket';
        document.getElementById('pkgModalAddLink').href = '{{ route('admin.packages.index') }}?customer_id=' + customerId;
        document.getElementById('pkgModalSearchInput').value = '';

        const tbody = document.getElementById('pkgModalTableBody');
        tbody.innerHTML = '';

        if (packages && packages.length > 0) {
            packages.forEach(pkg => {
                const prod = pkg.product || {};
                const netUsd = parseFloat(prod.net_price || 0);
                const netEur = parseFloat(netUsd * USD_TO_EUR_RATE);
                const salePrice = parseFloat(pkg.sale_price || 0);
                const profit = salePrice - netEur;

                const tr = document.createElement('tr');
                tr.className = 'pkg-modal-row hover:bg-slate-50 transition';
                tr.setAttribute('data-name', ((prod.product_name || '') + ' ' + (prod.product_code || '')).toLowerCase());
                tr.innerHTML = `
                    <td class="py-3 px-3">
                        <div class="font-bold text-slate-900">${prod.product_name || 'Bilinmeyen Paket'}</div>
                        <div class="text-[10px] font-mono text-blue-600 font-semibold">${prod.product_code || ''}</div>
                    </td>
                    <td class="py-3 px-3">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                            ${prod.data_total || ''} ${prod.data_unit || ''}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-right font-medium text-slate-500">
                        <div>€${netEur.toFixed(2)}</div>
                        <div class="text-[10px] font-mono text-slate-400">($${netUsd.toFixed(2)})</div>
                    </td>
                    <td class="py-3 px-3 text-right font-black text-slate-900 text-sm">
                        €${salePrice.toFixed(2)}
                    </td>
                    <td class="py-3 px-3 text-right font-black text-emerald-600">
                        +€${profit.toFixed(2)}
                    </td>
                    <td class="py-3 px-3 text-center">
                        <form action="/admin/packages/assignment/${pkg.id}" method="POST" class="inline" onsubmit="return confirm('Bu paketi müşteriden kaldırmak istediğinize emin misiniz?');">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Paket Atamasını Kaldır">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </form>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400 font-medium">
                        Bu müşteriye henüz atanmış bir paket bulunmuyor.
                    </td>
                </tr>
            `;
        }

        document.getElementById('customerPackagesModal').classList.remove('hidden');
    }

    function filterPkgModalTable() {
        const query = (document.getElementById('pkgModalSearchInput').value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.pkg-modal-row');
        rows.forEach(r => {
            const name = r.getAttribute('data-name') || '';
            if (!query || name.includes(query)) {
                r.style.display = '';
            } else {
                r.style.display = 'none';
            }
        });
    }

    function openBalanceModal(customerId, customerName, currentBalance) {
        document.getElementById('balanceCustomerName').innerText = customerName;
        document.getElementById('balanceCurrentAmount').innerText = '€' + parseFloat(currentBalance).toFixed(2);
        document.getElementById('balanceForm').action = '/admin/customers/' + customerId + '/balance';
        document.getElementById('balanceModal').classList.remove('hidden');
    }

    function openPasswordModal(customerId, customerName) {
        document.getElementById('passwordCustomerName').innerText = customerName;
        document.getElementById('passwordForm').action = '/admin/customers/' + customerId + '/password';
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    function openBranchModal(customerId, customerName, branches) {
        document.getElementById('branchModalCustomerName').innerText = customerName;
        document.getElementById('adminAddBranchForm').action = '/admin/customers/' + customerId + '/branches';

        const container = document.getElementById('branchListContainer');
        container.innerHTML = '';

        if (branches && branches.length > 0) {
            branches.forEach(b => {
                const item = document.createElement('div');
                item.className = 'flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs';
                item.innerHTML = `
                    <div>
                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-location-dot text-rose-500"></i> ${b.name}
                        </div>
                        ${b.address ? `<div class="text-slate-500 text-[11px]">${b.address}</div>` : ''}
                    </div>
                    <form action="/admin/branches/${b.id}" method="POST" onsubmit="return confirm('Şubeyi silmek istediğinize emin misiniz?');">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 rounded bg-rose-50 border border-rose-200">Sil</button>
                    </form>
                `;
                container.appendChild(item);
            });
        } else {
            container.innerHTML = '<div class="text-xs text-slate-400 py-3 text-center">Henüz şube tanımlanmamış.</div>';
        }

        document.getElementById('adminBranchModal').classList.remove('hidden');
    }
</script>
@endsection
