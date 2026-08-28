<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-50 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POLO SIM — ONE SIM ONE WORLD')</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- QRCode.js CDN for client-side QR rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            500: '#2563eb',
                            600: '#1d4ed8',
                            700: '#1e40af',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-panel {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .glow-blue {
            box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.15);
        }
        .glow-emerald {
            box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.15);
        }

        /* ── Global Page Loading Overlay ── */
        #pageLoader {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 18px;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s ease;
        }
        #pageLoader.visible { opacity: 1; pointer-events: all; }
        #pageLoader .loader-card {
            background: #fff; border-radius: 20px; padding: 28px 40px;
            display: flex; flex-direction: column; align-items: center; gap: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2); min-width: 240px;
        }
        #pageLoader .spinner {
            width: 44px; height: 44px;
            border: 4px solid #e2e8f0; border-top-color: #2563eb;
            border-radius: 50%; animation: spin 0.75s linear infinite;
        }
        #pageLoader .loader-text { font-size: 14px; font-weight: 700; color: #1e293b; }
        #pageLoader .loader-sub { font-size: 11px; color: #94a3b8; font-weight: 500; margin-top: -8px; }
        #topProgressBar {
            position: fixed; top: 0; left: 0; height: 3px; width: 0%;
            background: linear-gradient(90deg, #2563eb, #6366f1);
            z-index: 10000; transition: width 0.4s ease;
            border-radius: 0 3px 3px 0; box-shadow: 0 0 8px rgba(99,102,241,0.6);
        }
        @keyframes spin { to { transform: rotate(360deg); } }    </style>
</head>
<body class="h-full font-sans antialiased flex flex-col md:flex-row bg-slate-50 min-h-screen text-slate-800 selection:bg-blue-600 selection:text-white">
    <!-- ── Global Page Loader ── -->
    <div id="topProgressBar" style="width:20%"></div>
    <div id="pageLoader" class="visible">
        <div class="loader-card">
            <div class="spinner"></div>
            <div class="loader-text" id="loaderText">Sayfa yükleniyor...</div>
            <div class="loader-sub" id="loaderSub">Hazırlanıyor</div>
        </div>
    </div>


    @auth
    <!-- Mobile Header Bar (Only visible on mobile screens) -->
    <header class="md:hidden glass-panel border-b border-slate-200 p-4 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <div class="bg-white p-1 rounded-xl shadow border border-slate-200 shrink-0">
                <img src="/images/logo.png" alt="POLO SIM" class="h-7 w-auto object-contain">
            </div>
            <div>
                <h1 class="font-bold text-sm text-slate-900 tracking-wide leading-tight uppercase font-mono">POLO SIM</h1>
                <span class="text-[9px] text-amber-600 font-bold tracking-wider uppercase block">ONE SIM ONE WORLD</span>
            </div>
        </div>
        <button onclick="toggleMobileMenu()" class="p-2.5 rounded-xl bg-slate-100 border border-slate-300 text-slate-700 hover:text-slate-900 focus:outline-none shadow-sm">
            <i id="mobileMenuIcon" class="fa-solid fa-bars text-lg"></i>
        </button>
    </header>

    <!-- Sidebar Navigation (Hidden by default on mobile, collapsible via toggle) -->
    <aside id="sidebarMenu" class="hidden md:flex w-full md:w-64 glass-panel border-r border-slate-200 flex-col justify-between shrink-0">
        <div>
            <!-- Desktop Brand Logo Header -->
            <div class="hidden md:flex p-5 border-b border-slate-100 items-center gap-3">
                <div class="bg-white p-1.5 rounded-xl shadow border border-slate-200 shrink-0">
                    <img src="/images/logo.png" alt="POLO SIM" class="h-9 w-auto object-contain">
                </div>
                <div class="truncate">
                    <h1 class="font-bold text-base text-slate-900 tracking-wide leading-tight uppercase font-mono">POLO SIM</h1>
                    <span class="text-[10px] text-amber-600 font-bold tracking-wider uppercase block">ONE SIM ONE WORLD</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                @if(Auth::user()->isAdmin())
                    <div class="px-3 py-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Yönetim Menüsü</div>

                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-blue-600"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-users w-5 text-center text-blue-600"></i>
                        <span>Müşteri Yönetimi</span>
                    </a>

                    <a href="{{ route('admin.packages.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.packages.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-box-open w-5 text-center text-blue-600"></i>
                        <span>Paketler & Fiyatlama</span>
                    </a>

                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-blue-600"></i>
                        <span>Satış & Kâr Raporu</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-sliders w-5 text-center text-blue-600"></i>
                        <span>API Ayarları</span>
                    </a>

                @else
                    <div class="px-3 py-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Müşteri Portalı</div>

                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('customer.dashboard') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-qrcode w-5 text-center text-blue-600"></i>
                        <span>Paketlerim & Mağaza</span>
                    </a>

                    <a href="{{ route('customer.branches.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('customer.branches.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-store w-5 text-center text-blue-600"></i>
                        <span>Şubelerim</span>
                    </a>
                @endif

                <div class="pt-2">
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-700 border border-blue-200 font-bold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-user-gear w-5 text-center text-blue-600"></i>
                        <span>Profil & Şifre</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- User Footer Profile -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/80">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white shrink-0 shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="font-bold text-sm text-slate-900 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-500 capitalize flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full {{ Auth::user()->isAdmin() ? 'bg-emerald-500' : 'bg-blue-500' }}"></span>
                            {{ Auth::user()->role === 'admin' ? 'Yönetici' : 'Müşteri' }}
                        </div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-2 rounded-lg transition duration-200" title="Çıkış Yap">
                        <i class="fa-solid fa-right-from-bracket text-base"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    @endauth

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

        <!-- Flash Alert Messages -->
        <div class="p-4 max-w-7xl w-full mx-auto pb-0">
            @if (session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded-xl shadow-sm mb-4 flex items-center justify-between border border-emerald-200">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-xl"></i>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 rounded-xl shadow-sm mb-4 flex items-center justify-between border border-rose-200">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-xl"></i>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
                </div>
            @endif
        </div>

        <div class="p-4 md:p-8 max-w-7xl w-full mx-auto flex-1">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="p-4 border-t border-slate-200 text-center text-xs text-slate-500 bg-white">
            POLO SIM Portal &copy; {{ date('Y') }} — Tüm Hakları Saklıdır.
        </footer>
    </main>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('sidebarMenu');
            const icon = document.getElementById('mobileMenuIcon');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                menu.classList.add('flex');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
            } else {
                menu.classList.remove('flex');
                menu.classList.add('hidden');
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
            }
        }
    </script>

        <script>
        /* ── Global Loader Controller ── */
        const pageLoader  = document.getElementById('pageLoader');
        const progressBar = document.getElementById('topProgressBar');
        const loaderText  = document.getElementById('loaderText');
        const loaderSub   = document.getElementById('loaderSub');

        function showLoader(text, sub) {
            loaderText.textContent = text || 'İşlem yapılıyor...';
            loaderSub.textContent  = sub  || 'Lütfen bekleyin';
            pageLoader.classList.add('visible');
            progressBar.style.width = '30%';
            setTimeout(() => { progressBar.style.width = '70%'; }, 400);
        }

        function hideLoader() {
            progressBar.style.width = '100%';
            setTimeout(() => {
                pageLoader.classList.remove('visible');
                progressBar.style.width = '0%';
            }, 300);
        }

        // Auto-show on form submit (all forms except search/filter forms)
        document.addEventListener('submit', function(e) {
            const form = e.target;
            // Skip forms with data-no-loader attribute
            if (form.hasAttribute('data-no-loader')) return;
            // Skip GET forms (search/filter)
            if ((form.getAttribute('method') || 'GET').toUpperCase() === 'GET') return;

            const submitBtn = form.querySelector('[type="submit"]');
            let text = 'İşlem yapılıyor...';
            let sub  = 'Lütfen bekleyin';

            if (submitBtn) {
                const btnText = submitBtn.innerText.trim();
                if (btnText.includes('Paket')) { text = 'Paket işlemi yapılıyor...'; sub = 'Bu işlem biraz sürebilir'; }
                else if (btnText.includes('Sync') || btnText.includes('Çek')) { text = 'TGT API\'den paketler çekiliyor...'; sub = 'Binlerce paket getiriliyor, bekleyin'; }
                else if (btnText.includes('Atama') || btnText.includes('Ata')) { text = 'Paketler atanıyor...'; sub = 'Müşteri atamaları yapılıyor'; }
                else if (btnText.includes('Kaydet') || btnText.includes('Güncelle')) { text = 'Kaydediliyor...'; sub = ''; }
                else if (btnText.includes('Giriş')) { text = 'Giriş yapılıyor...'; sub = ''; }
                else if (btnText.includes('Satın') || btnText.includes('Al')) { text = 'eSIM satın alınıyor...'; sub = 'TGT API üzerinden sipariş açılıyor'; }
            }

            showLoader(text, sub);
        });

        // Auto-show on sidebar/nav links click (page navigation)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href]').forEach(function(link) {
                const href = link.getAttribute('href');
                // Only internal links that cause navigation, skip # anchors and JS links
                if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto')) return;
                // Skip links with data-no-loader
                if (link.hasAttribute('data-no-loader')) return;
                link.addEventListener('click', function(e) {
                    if (e.ctrlKey || e.metaKey || e.shiftKey) return; // allow open-in-new-tab
                    showLoader('Sayfa yükleniyor...', '');
                });
            });
        });

        // Hide it once HTML is parsed (don't wait for all images/frames to load)
        document.addEventListener('DOMContentLoaded', function() {
            hideLoader();
        });

        // Hide loader when browser fires pageshow (covers back/forward navigation)
        window.addEventListener('pageshow', function() {
            hideLoader();
        });
    </script>
@stack('scripts')
</body>
</html>


