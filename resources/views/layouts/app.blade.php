<!DOCTYPE html>
<html lang="tr" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TGT eSIM Platformu')</title>
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
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-panel {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glow-blue {
            box-shadow: 0 0 25px -5px rgba(59, 130, 246, 0.3);
        }
        .glow-emerald {
            box-shadow: 0 0 25px -5px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>
<body class="h-full font-sans antialiased flex flex-col md:flex-row bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 min-h-screen text-slate-200 selection:bg-blue-500 selection:text-white">

    @auth
    <!-- Sidebar Navigation -->
    <aside class="w-full md:w-64 glass-panel border-r border-slate-800/80 flex flex-col justify-between shrink-0">
        <div>
            <!-- Brand Logo Header -->
            <div class="p-5 border-b border-slate-800/60 flex items-center gap-3">
                <div class="bg-white/90 p-1.5 rounded-xl shadow-lg glow-blue shrink-0">
                    <img src="/images/logo.png" alt="POLO SIM" class="h-9 w-auto object-contain">
                </div>
                <div class="truncate">
                    <h1 class="font-bold text-base text-white tracking-wide leading-tight uppercase font-mono">POLO SIM</h1>
                    <span class="text-[10px] text-amber-400 font-semibold tracking-wider uppercase block">ONE SIM ONE WORLD</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5">
                @if(Auth::user()->isAdmin())
                    <div class="px-3 py-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Yönetim Menüsü</div>

                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>

                    <a href="{{ route('admin.customers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span>Müşteri Yönetimi</span>
                    </a>

                    <a href="{{ route('admin.packages.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.packages.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <i class="fa-solid fa-box-open w-5 text-center"></i>
                        <span>Paketler & Fiyatlama</span>
                    </a>

                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.reports.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i>
                        <span>Satış & Kâr Raporu</span>
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <i class="fa-solid fa-sliders w-5 text-center"></i>
                        <span>TGT API Ayarları</span>
                    </a>

                @else
                    <div class="px-3 py-1.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Müşteri Portalı</div>

                    <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-200 {{ request()->routeIs('customer.dashboard') ? 'bg-blue-600/20 text-blue-400 border border-blue-500/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50' }}">
                        <i class="fa-solid fa-qrcode w-5 text-center"></i>
                        <span>Paketlerim & Mağaza</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- User Footer Profile -->
        <div class="p-4 border-t border-slate-800/60 bg-slate-900/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-white shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="font-medium text-sm text-slate-200 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-slate-400 capitalize flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full {{ Auth::user()->isAdmin() ? 'bg-emerald-400' : 'bg-blue-400' }}"></span>
                            {{ Auth::user()->role === 'admin' ? 'Yönetici' : 'Müşteri' }}
                        </div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-400 p-2 rounded-lg transition duration-200" title="Çıkış Yap">
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
                <div class="glass-card border-l-4 border-emerald-500 text-emerald-300 p-4 rounded-xl shadow-lg mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-xl"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="glass-card border-l-4 border-rose-500 text-rose-300 p-4 rounded-xl shadow-lg mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-400 text-xl"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
                </div>
            @endif
        </div>

        <div class="p-4 md:p-8 max-w-7xl w-full mx-auto flex-1">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="p-4 border-t border-slate-800/40 text-center text-xs text-slate-500">
            TGT Technology Global-eSIM API 2.0 Integration &copy; {{ date('Y') }} — Tüm Hakları Saklıdır.
        </footer>
    </main>

    @stack('scripts')
</body>
</html>
