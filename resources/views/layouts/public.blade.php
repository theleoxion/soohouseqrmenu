<!DOCTYPE html>
<html lang="tr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $brandName = $siteSettings['brand_name'] ?? 'Soohouse Coffee®';
        $pageTitle = $metaTitle ?? ($seo->meta_title ?? $brandName . ' | Topraktan Fincana Nitelikli Kahve');
        $pageDesc = $metaDescription ?? ($seo->meta_description ?? 'Soohouse Coffee® nitelikli kahve çekirdekleri, şubelerimiz, dijital QR menümüz ve franchise ekosistemimiz.');
        $canonicalUrl = $canonical ?? ($seo->canonical_url ?? url()->current());
        $ogTitle = $seo->og_title ?? $pageTitle;
        $ogDesc = $seo->og_description ?? $pageDesc;
        $ogImage = !empty($seo->og_image) ? asset($seo->og_image) : asset('brand/soohouse-logo-1200.png');
        $isNoIndex = ($noindex ?? false) || ($seo->noindex ?? false);
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @if($isNoIndex)
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow, max-image-preview:large">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:site_name" content="{{ $brandName }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDesc }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Favicons & Web Manifest -->
    <link rel="icon" href="{{ asset($siteSettings['favicon'] ?? 'favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('soohouse-icon-180.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="{{ $siteSettings['primary_color'] ?? '#0D0D0C' }}">

    <!-- Preload Self-Hosted Fonts -->
    <link rel="preload" href="/fonts/space-grotesk-latin-ext-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/plus-jakarta-sans-latin-ext-wght-normal.woff2" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-obsidian: {{ $siteSettings['primary_color'] ?? '#0D0D0C' }};
            --color-amber-harvest: {{ $siteSettings['accent_color'] ?? '#C87D3B' }};
            --color-cream: {{ $siteSettings['bg_color'] ?? '#F8F5F0' }};
        }
    </style>

    <!-- Organization & WebSite JSON-LD Structured Data -->
    @php
        $orgJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $brandName,
            'url' => config('app.url'),
            'logo' => asset('brand/soohouse-logo-1200.png'),
        ];
        if (!empty($siteSettings['hq_phone'])) {
            $orgJsonLd['telephone'] = $siteSettings['hq_phone'];
        }
        if (!empty($siteSettings['hq_email'])) {
            $orgJsonLd['email'] = $siteSettings['hq_email'];
        }
        $sameAs = array_values(array_filter([
            $siteSettings['instagram_url'] ?? null,
            $siteSettings['facebook_url'] ?? null,
            $siteSettings['linkedin_url'] ?? null,
            $siteSettings['youtube_url'] ?? null,
        ]));
        if (!empty($sameAs)) {
            $orgJsonLd['sameAs'] = $sameAs;
        }
        $websiteJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $brandName,
            'url' => config('app.url'),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($orgJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @stack('structured_data')
    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-cream text-obsidian antialiased selection:bg-amber-harvest selection:text-white" x-data="{ mobileMenuOpen: false }">

    <!-- Top Brand Announcement Bar -->
    <div class="bg-obsidian text-cream/85 text-xs border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
            <div class="flex items-center gap-2 font-medium tracking-wide">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-harvest"></span>
                <span>{{ $siteSettings['brand_slogan'] ?? 'Topraktan Fincana Nitelikli Kahve Deneyimi' }}</span>
            </div>
            <div class="hidden sm:flex items-center gap-5">
                <a href="{{ route('public.branches') }}" class="hover:text-amber-harvest transition-colors">Şube &amp; QR Menü Noktalarımız</a>
                <span class="text-white/20">|</span>
                <a href="{{ route('public.franchise') }}" class="text-amber-light hover:text-amber-harvest font-semibold transition-colors">Yatırımcı &amp; Franchise Başvurusu →</a>
            </div>
        </div>
    </div>

    <!-- Main Sticky Navigation Header -->
    <header class="sticky top-0 z-40 bg-cream/95 backdrop-blur-md border-b border-obsidian/10 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <!-- Brand Logo -->
            <a href="{{ route('public.home') }}" class="flex items-center gap-3 group focus:outline-none" aria-label="{{ $brandName }} Ana Sayfa">
                <img src="{{ asset($siteSettings['logo_light'] ?? 'brand/soohouse-logo.svg') }}"
                     alt="{{ $brandName }}"
                     width="190"
                     height="95"
                     class="h-12 sm:h-14 w-auto object-contain transition-transform duration-300 group-hover:scale-[1.02]">
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden lg:flex items-center gap-1 xl:gap-2" aria-label="Ana Menü">
                @php
                    $navLinks = [
                        ['route' => 'public.home', 'label' => 'Ana Sayfa'],
                        ['route' => 'public.about', 'label' => 'Biz Kimiz'],
                        ['route' => 'public.products', 'label' => 'Ürünlerimiz'],
                        ['route' => 'public.branches', 'label' => 'Şubelerimiz'],
                        ['route' => 'public.franchise', 'label' => 'Franchise'],
                        ['route' => 'public.press', 'label' => 'Basında Biz'],
                        ['route' => 'public.contact', 'label' => 'İletişim'],
                    ];
                @endphp
                @foreach($navLinks as $item)
                    @php $active = request()->routeIs($item['route']) || ($item['route'] === 'public.branches' && request()->routeIs('public.branch.show')); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="px-3.5 py-2 rounded-full text-sm font-semibold tracking-tight transition-all {{ $active ? 'bg-obsidian text-cream shadow-sm' : 'text-obsidian/80 hover:text-obsidian hover:bg-sand/70' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <!-- Desktop CTA Buttons -->
            <div class="hidden lg:flex items-center gap-3">
                <a href="{{ route('public.branches') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full border border-obsidian/20 text-xs font-bold uppercase tracking-wider text-obsidian hover:border-obsidian hover:bg-sand/50 transition-all">
                    <svg class="w-4 h-4 text-amber-harvest" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    QR Menü
                </a>
                <a href="{{ route('public.franchise') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider hover:bg-roast transition-all shadow-sm">
                    Franchise Başvurusu
                </a>
            </div>

            <!-- Mobile Hamburger Button -->
            <button type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    :aria-expanded="mobileMenuOpen"
                    aria-label="Menüyü Aç/Kapat"
                    class="lg:hidden inline-flex items-center justify-center w-11 h-11 rounded-xl border border-obsidian/15 bg-white/80 text-obsidian hover:bg-sand transition">
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Drawer -->
        <div x-show="mobileMenuOpen"
             x-cloak
             x-transition.opacity
             class="lg:hidden border-t border-obsidian/10 bg-cream px-4 pt-4 pb-6 shadow-xl">
            <nav class="flex flex-col gap-1.5" aria-label="Mobil Menü">
                @foreach($navLinks as $item)
                    @php $active = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="px-4 py-3 rounded-xl text-base font-semibold {{ $active ? 'bg-obsidian text-cream' : 'text-obsidian hover:bg-sand' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="mt-4 pt-4 border-t border-obsidian/10 grid grid-cols-2 gap-3">
                <a href="{{ route('public.branches') }}" class="flex items-center justify-center py-3 px-4 rounded-xl border border-obsidian/20 text-xs font-bold uppercase tracking-wider text-obsidian">
                    Şubeler &amp; QR Menü
                </a>
                <a href="{{ route('public.franchise') }}" class="flex items-center justify-center py-3 px-4 rounded-xl bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider">
                    Franchise Başvur
                </a>
            </div>
        </div>
    </header>

    <!-- Optional Breadcrumb Bar -->
    @if(!empty($breadcrumbs))
        <nav aria-label="Breadcrumb" class="bg-sand/50 border-b border-obsidian/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
                <ol class="flex flex-wrap items-center gap-2 text-xs text-obsidian/70">
                    <li>
                        <a href="{{ route('public.home') }}" class="hover:text-obsidian font-medium">Ana Sayfa</a>
                    </li>
                    @foreach($breadcrumbs as $bc)
                        <li class="flex items-center gap-2">
                            <span class="text-obsidian/30">/</span>
                            @if(!empty($bc['url']) && !$loop->last)
                                <a href="{{ $bc['url'] }}" class="hover:text-obsidian font-medium">{{ $bc['name'] }}</a>
                            @else
                                <span class="text-obsidian font-semibold" aria-current="page">{{ $bc['name'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </nav>
        @php
            $bcList = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Ana Sayfa',
                        'item' => route('public.home'),
                    ],
                ],
            ];
            foreach ($breadcrumbs as $idx => $bc) {
                $bcList['itemListElement'][] = [
                    '@type' => 'ListItem',
                    'position' => $idx + 2,
                    'name' => $bc['name'],
                    'item' => $bc['url'] ?? url()->current(),
                ];
            }
        @endphp
        <script type="application/ld+json">{!! json_encode($bcList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Corporate Footer -->
    <footer class="bg-obsidian text-cream border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-10">
                <!-- Col 1: Brand Identity -->
                <div class="lg:col-span-4 space-y-5">
                    <div class="inline-flex items-center gap-3 bg-cream px-4 py-2.5 rounded-xl">
                        <img src="{{ asset($siteSettings['logo_light'] ?? 'brand/soohouse-logo.svg') }}"
                             alt="{{ $brandName }}"
                             width="180"
                             height="90"
                             loading="lazy"
                             class="h-11 w-auto object-contain">
                    </div>
                    <p class="text-sm text-cream/75 leading-relaxed max-w-sm">
                        {{ $siteSettings['about_lead'] ?? 'Soohouse Coffee®, nitelikli kahve çekirdeklerini topraktan fincana uzanan zanaatkâr kavrum disiplini ve güçlü franchise ekosistemiyle buluşturur.' }}
                    </p>
                    <div class="flex items-center gap-3 pt-1">
                        @if(!empty($siteSettings['instagram_url']))
                            <a href="{{ $siteSettings['instagram_url'] }}" target="_blank" rel="noopener" aria-label="Instagram" class="w-10 h-10 rounded-full bg-white/10 hover:bg-amber-harvest flex items-center justify-center transition">
                                <span class="text-xs font-bold">IG</span>
                            </a>
                        @endif
                        @if(!empty($siteSettings['facebook_url']))
                            <a href="{{ $siteSettings['facebook_url'] }}" target="_blank" rel="noopener" aria-label="Facebook" class="w-10 h-10 rounded-full bg-white/10 hover:bg-amber-harvest flex items-center justify-center transition">
                                <span class="text-xs font-bold">FB</span>
                            </a>
                        @endif
                        @if(!empty($siteSettings['linkedin_url']))
                            <a href="{{ $siteSettings['linkedin_url'] }}" target="_blank" rel="noopener" aria-label="LinkedIn" class="w-10 h-10 rounded-full bg-white/10 hover:bg-amber-harvest flex items-center justify-center transition">
                                <span class="text-xs font-bold">IN</span>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Col 2: Quick Corporate Links -->
                <div class="lg:col-span-3 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-amber-harvest">Kurumsal Keşif</h3>
                    <ul class="space-y-2.5 text-sm text-cream/80">
                        <li><a href="{{ route('public.about') }}" class="hover:text-amber-light transition">Biz Kimiz &amp; Kahve Felsefemiz</a></li>
                        <li><a href="{{ route('public.products') }}" class="hover:text-amber-light transition">Kahve Çekirdekleri &amp; Ürünlerimiz</a></li>
                        <li><a href="{{ route('public.branches') }}" class="hover:text-amber-light transition">Şubelerimiz &amp; Harita</a></li>
                        <li><a href="{{ route('public.franchise') }}" class="hover:text-amber-light transition">Franchise Başvuru Formu</a></li>
                        <li><a href="{{ route('public.press') }}" class="hover:text-amber-light transition">Basında Biz</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-amber-light transition">Genel Merkez İletişim</a></li>
                    </ul>
                </div>

                <!-- Col 3: Franchise & Digital Menu Ecosystem -->
                <div class="lg:col-span-5 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-amber-harvest">Yatırımcı &amp; Genel Merkez</h3>
                    <div class="p-6 rounded-2xl bg-charcoal border border-white/10 space-y-3">
                        <p class="text-sm font-semibold text-white">Kendi Soohouse Coffee® Şubenizi Açın</p>
                        <p class="text-xs text-cream/70 leading-relaxed">
                            Anahtar teslim mimari konsept, merkezi kavrum tedariği, barista eğitimi ve uçtan uca dijital şube yönetim altyapısıyla yatırımcı ekosistemimize katılın.
                        </p>
                        <div class="pt-2 flex flex-wrap items-center gap-3">
                            <a href="{{ route('public.franchise') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider hover:bg-amber-light hover:text-obsidian transition">
                                Başvuru Formuna Git →
                            </a>
                            @if(!empty($siteSettings['hq_phone']))
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSettings['hq_phone']) }}" class="text-xs text-cream/80 hover:text-white font-medium">
                                    {{ $siteSettings['hq_phone'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-cream/50">
                <p>© {{ date('Y') }} {{ $brandName }}. Tüm hakları saklıdır.</p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('public.branches') }}" class="hover:text-cream transition">Dijital QR Menü</a>
                    <a href="{{ route('seo.sitemap') }}" class="hover:text-cream transition">Site Haritası</a>
                    <a href="{{ route('login') }}" class="hover:text-cream transition">Yönetim Girişi</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
