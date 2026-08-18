@extends('layouts.app')

@section('title', 'Şubelerim & Personel Yönetimi — POLO SIM')

@section('content')
<div class="space-y-6 max-w-4xl">
    <!-- Header & Add Action -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-store text-blue-600"></i>
                <span>Şube & Personel Yönetimi</span>
            </h1>
            <p class="text-slate-500 text-sm mt-1">Şubelerinizi tanımlayın ve her şubenize özel giriş yapıp satış yapacak personel hesapları oluşturun.</p>
        </div>
        <button onclick="document.getElementById('createBranchModal').classList.remove('hidden')" 
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2 active:scale-95">
            <i class="fa-solid fa-plus"></i>
            <span>Yeni Şube Ekle</span>
        </button>
    </div>

    <!-- Branch List Grid -->
    <div class="grid grid-cols-1 gap-6">
        @forelse($branches as $branch)
            <div class="glass-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <div class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-location-dot text-rose-500"></i>
                            <span>{{ $branch->name }}</span>
                        </div>
                        @if($branch->address)
                            <div class="text-xs text-slate-500 mt-0.5 font-medium">{{ $branch->address }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-full text-xs font-black bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $branch->orders_count }} Sipariş
                        </span>
                        <button onclick="openStaffModal({{ $branch->id }}, '{{ addslashes($branch->name) }}')" 
                            class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-xl border border-indigo-200 transition flex items-center gap-1">
                            <i class="fa-solid fa-user-plus text-xs"></i> Personel Ekle
                        </button>
                        <form action="{{ route('customer.branches.destroy', $branch->id) }}" method="POST" onsubmit="return confirm('Bu şubeyi silmek istediğinize emin misiniz?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-2.5 py-1.5 text-rose-600 hover:text-rose-800 text-xs font-bold transition">
                                <i class="fa-solid fa-trash-can"></i> Sil
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Staff List Under Branch -->
                <div class="space-y-2">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                        <span>Şube Personelleri (Giriş Yetkilileri)</span>
                        <span class="text-[11px] font-semibold text-slate-500">{{ $branch->staff_count }} Personel</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @forelse($branch->staff as $staffUser)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                                <div>
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <i class="fa-solid fa-user text-indigo-600"></i>
                                        <span>{{ $staffUser->name }}</span>
                                    </div>
                                    <div class="font-mono text-[11px] text-slate-500 mt-0.5">{{ $staffUser->email }}</div>
                                </div>
                                <form action="{{ route('customer.branches.staff.destroy', $staffUser->id) }}" method="POST" onsubmit="return confirm('Bu personel hesabını silmek istediğinize emin misiniz?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold px-2 py-1 bg-rose-50 border border-rose-200 rounded">
                                        Sil
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="col-span-full p-3 bg-slate-50 rounded-xl text-center text-slate-400 text-xs font-medium border border-dashed border-slate-200">
                                Henüz bu şubeye özel bir personel tanımlanmamış.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-panel p-12 rounded-2xl text-center text-slate-400 bg-white border border-slate-200 space-y-2">
                <i class="fa-solid fa-store text-4xl text-slate-300"></i>
                <div class="text-base font-bold text-slate-700">Henüz tanımlı bir şubeniz bulunmuyor.</div>
                <div class="text-xs text-slate-500">İşlemlerinizin nereden yapıldığını ayırmak için "Yeni Şube Ekle" butonunu kullanabilirsiniz.</div>
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

<!-- Modal: Add Branch Staff -->
<div id="addStaffModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4">
    <div class="bg-white max-w-md w-full p-6 rounded-3xl shadow-2xl relative border border-slate-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-indigo-600"></i>
                    <span>Şubeye Personel Hesabı Ekle</span>
                </h3>
                <div id="staffModalBranchName" class="text-xs text-slate-500 font-bold"></div>
            </div>
            <button onclick="document.getElementById('addStaffModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
        </div>

        <form id="addStaffForm" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Personel Ad Soyad</label>
                <input type="text" name="name" required placeholder="Örn: Ali Yılmaz" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-bold focus:outline-none focus:border-indigo-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Personel E-Postası (Giriş İçin)</label>
                <input type="email" name="email" required placeholder="bakirkoy@firmasi.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-indigo-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Giriş Şifresi</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-medium focus:outline-none focus:border-indigo-600">
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('addStaffModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm rounded-xl font-bold">İptal</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md">Personeli Kaydet</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStaffModal(branchId, branchName) {
        document.getElementById('staffModalBranchName').innerText = branchName;
        document.getElementById('addStaffForm').action = '/customer/branches/' + branchId + '/staff';
        document.getElementById('addStaffModal').classList.remove('hidden');
    }
</script>
@endsection
