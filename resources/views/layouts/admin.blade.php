<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Yönetim Paneli' }} — Soohouse Coffee®</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream text-obsidian antialiased" x-data="{ sidebarOpen: false }">
    @php
        /** @var \App\Models\User $authUser */
        $authUser = auth()->user();
        $branchIds = $authUser->accessibleBranchIds();
        $navBranches = \App\Models\Branch::when($branchIds !== null, fn($q) => $q->whereIn('id', $branchIds))->orderBy('name')->get();
    @endphp

    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Mobile Top Bar -->
        <div class="lg:hidden bg-obsidian text-cream px-4 h-16 flex items-center justify-between border-b border-white/10 sticky top-0 z-40">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                <span class="px-2.5 py-1 rounded bg-amber-harvest text-white font-display font-bold text-xs tracking-wider">SOOHOUSE®</span>
                <span class="text-xs font-semibold text-cream/80">YÖNETİM</span>
            </a>
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg bg-white/10 text-cream" aria-label="Menü">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-obsidian text-cream flex flex-col border-r border-white/10 transition-transform duration-200 lg:static lg:translate-x-0 shrink-0">
            <!-- Brand Header -->
            <div class="p-5 border-b border-white/10 flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-cream flex items-center justify-center p-1.5 shrink-0">
                        <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="block font-display font-bold text-sm tracking-wider text-white">SOOHOUSE®</span>
                        <span class="block text-[11px] text-amber-light font-medium">Kurumsal Yönetim Platformu</span>
                    </div>
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-cream/60 hover:text-white">✕</button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-6 text-sm">
                <!-- Genel Bakış -->
                <div>
                    <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-cream/40">Genel Bakış</p>
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                        <span>Gösterge Paneli</span>
                    </a>
                </div>

                <!-- Genel Merkez Modülleri -->
                @if($authUser->hasPermission('branches.manage') || $authUser->hasPermission('categories.manage') || $authUser->hasPermission('products.manage') || $authUser->hasPermission('prices.bulk_update') || $authUser->hasPermission('excel.import') || $authUser->hasPermission('excel.export'))
                    <div>
                        <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-amber-harvest">Genel Merkez Yönetimi</p>
                        <div class="space-y-1">
                            @if($authUser->hasPermission('branches.manage'))
                                <a href="{{ route('admin.branches.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.branches.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Şube Yönetimi</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('categories.manage'))
                                <a href="{{ route('admin.categories.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.categories.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Kategori Yönetimi</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('products.manage'))
                                <a href="{{ route('admin.products.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.products.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Merkez Ürün Havuzu</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('prices.bulk_update'))
                                <a href="{{ route('admin.pricing.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.pricing.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Toplu Fiyat Yönetimi</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('excel.import') || $authUser->hasPermission('excel.export'))
                                <a href="{{ route('admin.excel.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.excel.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Excel İçe / Dışa Aktar</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Şube Operasyonları (Bağlı Şubeler) -->
                @if($authUser->hasPermission('branch_menu.manage') || $authUser->hasPermission('prices.branch_update') || $authUser->hasPermission('tables.manage') || $authUser->hasPermission('branch_settings.manage'))
                    <div>
                        <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-amber-harvest">Şube Operasyonları</p>
                        @if($navBranches->isEmpty())
                            <div class="px-3 py-2 text-xs text-cream/50 bg-white/5 rounded-xl">
                                Henüz atanmış veya eklenmiş şube bulunmuyor.
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($navBranches as $b)
                                    <div class="p-2.5 rounded-xl bg-white/5 border border-white/10 space-y-1">
                                        <div class="px-2 py-1 text-xs font-bold text-amber-light flex items-center justify-between">
                                            <span class="truncate">{{ $b->name }}</span>
                                            <a href="{{ route('qrmenu.show', $b->slug) }}" target="_blank" class="text-[10px] text-cream/60 hover:text-white underline shrink-0">QR ↗</a>
                                        </div>
                                        @if($authUser->hasPermission('branch_menu.manage') || $authUser->hasPermission('prices.branch_update'))
                                            <a href="{{ route('admin.branch.menu', $b->slug) }}"
                                               class="block px-2.5 py-1.5 rounded-lg text-xs font-medium {{ request()->fullUrlIs(route('admin.branch.menu', $b->slug) . '*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                                • Menü Seçimi &amp; Fiyat/İndirim
                                            </a>
                                        @endif
                                        @if($authUser->hasPermission('tables.manage'))
                                            <a href="{{ route('admin.branch.tables', $b->slug) }}"
                                               class="block px-2.5 py-1.5 rounded-lg text-xs font-medium {{ request()->fullUrlIs(route('admin.branch.tables', $b->slug) . '*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                                • Masalar &amp; Masa Kartı (QR)
                                            </a>
                                        @endif
                                        @if($authUser->hasPermission('branch_settings.manage') || $authUser->hasPermission('branches.manage'))
                                            <a href="{{ route('admin.branch.settings', $b->slug) }}"
                                               class="block px-2.5 py-1.5 rounded-lg text-xs font-medium {{ request()->fullUrlIs(route('admin.branch.settings', $b->slug) . '*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                                • Çalışma Saatleri &amp; İletişim
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Müşteri & Franchise İlişkileri -->
                @if($authUser->hasPermission('feedbacks.view') || $authUser->hasPermission('franchises.view'))
                    <div>
                        <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-amber-harvest">İletişim &amp; Başvurular</p>
                        <div class="space-y-1">
                            @if($authUser->hasPermission('feedbacks.view'))
                                <a href="{{ route('admin.feedbacks.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.feedbacks.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>İstek / Öneri / Şikayetler</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('franchises.view'))
                                <a href="{{ route('admin.franchises.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.franchises.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Franchise Başvuruları</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Süper Admin / Sistem Yönetimi -->
                @if($authUser->hasPermission('settings.manage') || $authUser->hasPermission('users.manage') || $authUser->hasPermission('roles.manage') || $authUser->hasPermission('audit_logs.view'))
                    <div>
                        <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-amber-harvest">Sistem &amp; Güvenlik</p>
                        <div class="space-y-1">
                            @if($authUser->hasPermission('settings.manage'))
                                <a href="{{ route('admin.settings.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Site, Tema, CMS &amp; SEO</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('users.manage'))
                                <a href="{{ route('admin.users.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Kullanıcılar &amp; Şube Kapsamı</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('roles.manage'))
                                <a href="{{ route('admin.roles.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.roles.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Roller &amp; İzin Matrisi</span>
                                </a>
                            @endif
                            @if($authUser->hasPermission('audit_logs.view'))
                                <a href="{{ route('admin.audit_logs.index') }}"
                                   class="flex items-center justify-between px-3 py-2.5 rounded-xl font-medium transition {{ request()->routeIs('admin.audit_logs.*') ? 'bg-amber-harvest text-white' : 'text-cream/80 hover:bg-white/10' }}">
                                    <span>Denetim Kayıtları (Audit Log)</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </nav>

            <!-- User Footer -->
            <div class="p-4 border-t border-white/10 bg-charcoal space-y-3">
                <div class="flex items-center justify-between">
                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate">{{ $authUser->name }}</p>
                        <p class="text-[11px] text-cream/60 truncate">{{ $authUser->roles->pluck('name')->implode(', ') ?: 'Kullanıcı' }}</p>
                    </div>
                    <a href="{{ route('admin.profile.edit') }}" class="text-xs text-amber-light hover:underline shrink-0">Profil</a>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('public.home') }}" target="_blank" class="flex-1 text-center py-2 px-3 rounded-lg bg-white/10 hover:bg-white/15 text-xs font-semibold text-cream transition">
                        Siteyi Gör ↗
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg bg-terracotta/90 hover:bg-terracotta text-xs font-semibold text-white transition cursor-pointer">
                            Çıkış Yap
                        </button>
                    </form>
                </div>
                <div class="pt-1 text-center">
                    <span class="text-[10px] text-cream/35 tracking-wider">Developed by <span class="text-cream/55 font-medium">Joyxen</span></span>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Bar -->
            <header class="bg-white border-b border-obsidian/10 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-display font-bold text-obsidian">{{ $title ?? 'Yönetim Paneli' }}</h1>
                    @if(!empty($subtitle))
                        <p class="text-xs text-obsidian/60 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @yield('header_actions')
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-6 pt-5 max-w-7xl w-full mx-auto">
                @if(session('status'))
                    <div class="mb-4 p-4 rounded-xl bg-sage/10 border border-sage/30 text-sage text-sm font-medium flex items-center justify-between">
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 rounded-xl bg-terracotta/10 border border-terracotta/30 text-terracotta text-sm space-y-1">
                        <p class="font-bold">Lütfen aşağıdaki alanları kontrol edin:</p>
                        <ul class="list-disc list-inside text-xs">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
