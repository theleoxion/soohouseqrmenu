@extends('layouts.public')

@section('content')
    <!-- HERO SECTION: From Farm to Cup & Franchise Ecosystem -->
    <section class="relative overflow-hidden bg-obsidian text-cream py-20 lg:py-28 border-b border-white/10">
        <!-- Decorative Background Pattern -->
        <div class="absolute inset-0 opacity-10 pointer-events-none"
             style="background-image: radial-gradient(#C87D3B 1px, transparent 1px); background-size: 32px 32px;"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Left Column: Brand Statement -->
                <div class="lg:col-span-7 space-y-7">
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-amber-light text-xs font-bold uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-amber-harvest"></span>
                        <span>{{ $siteSettings['hero_badge'] ?? 'SOOHOUSE COFFEE® — NİTELİKLİ KAHVE & FRANCHISE PLATFORMU' }}</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-display font-extrabold tracking-tight leading-[1.08] text-white">
                        {{ $siteSettings['hero_title'] ?? 'Toprağın Emeği, Kusursuz Kavrum.' }}
                    </h1>

                    <p class="text-base sm:text-lg text-cream/80 leading-relaxed max-w-2xl">
                        {{ $siteSettings['hero_subtitle'] ?? 'Soohouse Coffee olarak seçkin kahve çiftliklerinden özenle hasat edilen nitelikli çekirdekleri, kendine özgü kavrum profilimiz ve güçlü şube ekosistemimizle fincanınıza taşıyoruz.' }}
                    </p>

                    <div class="flex flex-wrap items-center gap-4 pt-2">
                        <a href="{{ route('public.branches') }}"
                           class="inline-flex items-center gap-3 px-7 py-4 rounded-full bg-amber-harvest text-white text-sm font-bold uppercase tracking-wider hover:bg-amber-light hover:text-obsidian transition shadow-lg">
                            <span>Şubelerimiz &amp; QR Menü</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        <a href="{{ route('public.franchise') }}"
                           class="inline-flex items-center gap-3 px-7 py-4 rounded-full bg-white/10 border border-white/20 text-cream text-sm font-bold uppercase tracking-wider hover:bg-white hover:text-obsidian transition">
                            <span>Franchise Başvurusu</span>
                        </a>
                    </div>

                    <!-- Value Pillars -->
                    <div class="pt-6 border-t border-white/10 grid grid-cols-3 gap-6 max-w-xl">
                        <div>
                            <p class="font-display font-bold text-lg text-amber-light">%100 Nitelikli</p>
                            <p class="text-xs text-cream/65 mt-0.5">İzlenebilir Hasat &amp; Çekirdek</p>
                        </div>
                        <div>
                            <p class="font-display font-bold text-lg text-amber-light">Merkezi Standart</p>
                            <p class="text-xs text-cream/65 mt-0.5">Tüm Şubelerde Aynı Reçete</p>
                        </div>
                        <div>
                            <p class="font-display font-bold text-lg text-amber-light">Güçlü Franchise</p>
                            <p class="text-xs text-cream/65 mt-0.5">Uçtan Uca Yatırımcı Desteği</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Emblem Showcase Card -->
                <div class="lg:col-span-5">
                    <div class="relative rounded-3xl bg-cream text-obsidian p-8 sm:p-10 shadow-2xl border-2 border-amber-harvest/40">
                        <div class="flex items-center justify-between border-b border-obsidian/10 pb-6 mb-8">
                            <span class="text-xs font-bold uppercase tracking-widest text-obsidian/60">Tescilli Marka Kimliği</span>
                            <span class="px-3 py-1 rounded-full bg-obsidian text-cream text-[11px] font-bold tracking-wider">EST. SOOHOUSE®</span>
                        </div>

                        <div class="py-6 flex flex-col items-center justify-center text-center">
                            <picture>
                                <source srcset="{{ asset('brand/soohouse-logo-640.webp') }}" type="image/webp">
                                <img src="{{ asset('brand/soohouse-logo.svg') }}"
                                     alt="Soohouse Coffee® Logo"
                                     width="320"
                                     height="160"
                                     class="w-64 sm:w-72 h-auto object-contain animate-float-slow">
                            </picture>
                            <p class="mt-8 text-sm text-obsidian/75 leading-relaxed max-w-sm">
                                Amblemimizdeki traktör; kahvenin fincana gelmeden önce toprağın, hasadın ve üretici emeğinin bir eseri olduğunu simgeler.
                            </p>
                        </div>

                        <div class="mt-6 pt-6 border-t border-obsidian/10 flex items-center justify-between text-xs font-semibold">
                            <a href="{{ route('public.products') }}" class="text-obsidian hover:text-amber-harvest transition">Kahve Çekirdeklerini İncele →</a>
                            <a href="{{ route('public.about') }}" class="text-amber-harvest hover:underline">Hikayemiz</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 2: THE TRACTOR & HARVEST PHILOSOPHY -->
    <section class="py-20 bg-cream border-b border-obsidian/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center space-y-4 reveal-up">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">KAHVE FELSEFEMİZ</span>
                <h2 class="text-3xl sm:text-4xl font-display font-bold text-obsidian">
                    {{ $siteSettings['Bean_section_title'] ?? 'Çiftlikten Fincana İzlenebilir Kalite' }}
                </h2>
                <p class="text-base text-obsidian/75 leading-relaxed">
                    {{ $siteSettings['Bean_section_text'] ?? 'Amblemimizdeki traktör, kahvenin kaynağındaki tarımsal emeğe ve toprağa duyduğumuz saygıyı simgeler. Her fincanda aynı standardı yakalamak için çekirdek seçiminden demleme reçetelerine kadar tüm süreçleri merkezden yönetiyoruz.' }}
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-white border border-obsidian/10 shadow-sm hover:shadow-md transition reveal-up">
                    <div class="w-12 h-12 rounded-2xl bg-obsidian text-amber-light flex items-center justify-center font-display font-bold text-lg mb-6">01</div>
                    <h3 class="text-xl font-display font-bold text-obsidian mb-3">Seçkin Hasat &amp; Çekirdek</h3>
                    <p class="text-sm text-obsidian/70 leading-relaxed">
                        Yüksek rakımlı kahve çiftliklerinden mevsiminde hasat edilen %100 Arabica ve nitelikli harman çekirdekler, aroma potansiyeline göre sınıflandırılır.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-white border border-obsidian/10 shadow-sm hover:shadow-md transition reveal-up">
                    <div class="w-12 h-12 rounded-2xl bg-obsidian text-amber-light flex items-center justify-center font-display font-bold text-lg mb-6">02</div>
                    <h3 class="text-xl font-display font-bold text-obsidian mb-3">Zanaatkâr Kavrum Profili</h3>
                    <p class="text-sm text-obsidian/70 leading-relaxed">
                        Her çekirdeğin meyvemsi, çikolatamsı veya karamelize gövde notalarını ortaya çıkaran hassas ısı-zaman eğrileriyle taze kavrum yapılır.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-white border border-obsidian/10 shadow-sm hover:shadow-md transition reveal-up">
                    <div class="w-12 h-12 rounded-2xl bg-obsidian text-amber-light flex items-center justify-center font-display font-bold text-lg mb-6">03</div>
                    <h3 class="text-xl font-display font-bold text-obsidian mb-3">Merkezi Dijital &amp; Barista Standardı</h3>
                    <p class="text-sm text-obsidian/70 leading-relaxed">
                        Hangi Soohouse Coffee® şubesine giderseniz gidin; kalibre edilmiş ekipmanlar, eğitimli baristalar ve canlı QR menü altyapımızla aynı deneyimi yaşarsınız.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 3: FEATURED COFFEE & MENU ITEMS (With Graceful Empty State) -->
    <section class="py-20 bg-sand/50 border-b border-obsidian/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">MERKEZ ÜRÜN HAVUZU</span>
                    <h2 class="text-3xl sm:text-4xl font-display font-bold text-obsidian mt-1">İmza Kahvelerimiz ve Lezzetlerimiz</h2>
                </div>
                <a href="{{ route('public.products') }}" class="inline-flex items-center gap-2 text-sm font-bold text-obsidian hover:text-amber-harvest transition">
                    <span>Tüm Ürün Kataloğunu Gör</span>
                    <span>→</span>
                </a>
            </div>

            @if($featuredProducts->isEmpty())
                <!-- Designed Empty State per Section 13 Data Policy -->
                <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-2xl mx-auto shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-sand mx-auto flex items-center justify-center mb-5">
                        <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-10 h-10 object-contain opacity-80">
                    </div>
                    <h3 class="text-xl font-display font-bold text-obsidian">Ürün Kataloğumuz Güncelleniyor</h3>
                    <p class="text-sm text-obsidian/70 mt-2 leading-relaxed">
                        Soohouse Coffee® nitelikli kahve çekirdekleri ve imza içecek koleksiyonumuz genel merkez yönetim paneli üzerinden eklendiğinde burada sergilenecektir.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('public.products') }}" class="px-5 py-2.5 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
                            Ürünlerimiz Sayfası
                        </a>
                        <a href="{{ route('public.franchise') }}" class="px-5 py-2.5 rounded-full border border-obsidian/20 text-obsidian text-xs font-bold uppercase tracking-wider hover:bg-sand transition">
                            Franchise Bilgisi Al
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
                    @foreach($featuredProducts as $product)
                        <article class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col">
                            @if($product->image)
                                <div class="aspect-16/10 bg-sand overflow-hidden">
                                    <img src="{{ asset($product->image) }}"
                                         alt="{{ $product->name }}"
                                         loading="lazy"
                                         class="w-full h-full object-cover hover:scale-105 transition duration-500">
                                </div>
                            @else
                                <div class="aspect-16/10 bg-obsidian text-cream flex flex-col items-center justify-center p-6 relative">
                                    <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="{{ $product->name }}" class="w-14 h-14 invert opacity-30">
                                    <span class="mt-2 text-[11px] uppercase tracking-widest text-amber-light font-semibold">{{ $product->category?->name ?? 'SOOHOUSE®' }}</span>
                                </div>
                            @endif

                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between gap-2 text-xs text-amber-harvest font-bold uppercase tracking-wider mb-1">
                                        <span>{{ $product->category?->name ?? 'Kahve' }}</span>
                                        @if($product->bean_origin)
                                            <span class="text-obsidian/50">{{ $product->bean_origin }}</span>
                                        @endif
                                    </div>
                                    <h3 class="text-lg font-display font-bold text-obsidian">{{ $product->name }}</h3>
                                    @if($product->short_description)
                                        <p class="text-sm text-obsidian/70 mt-1.5 line-clamp-2">{{ $product->short_description }}</p>
                                    @endif
                                </div>

                                <div class="pt-4 border-t border-obsidian/10 flex items-center justify-between">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(($product->temperature_options ?? []) as $temp)
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-sand text-obsidian">
                                                {{ $temp === 'hot' ? 'Sıcak' : 'Soğuk' }}
                                            </span>
                                        @endforeach
                                    </div>
                                    <span class="text-xs font-bold text-obsidian/60">Detay →</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- SECTION 4: OUR BRANCHES & QR MENU ACCESS (With Graceful Empty State) -->
    <section class="py-20 bg-cream border-b border-obsidian/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">ŞUBE AĞIMIZ &amp; DİJİTAL QR MENÜ</span>
                    <h2 class="text-3xl sm:text-4xl font-display font-bold text-obsidian mt-1">Soohouse Coffee® Noktaları</h2>
                </div>
                <a href="{{ route('public.branches') }}" class="inline-flex items-center gap-2 text-sm font-bold text-obsidian hover:text-amber-harvest transition">
                    <span>Tüm Şubeleri &amp; Haritayı Aç</span>
                    <span>→</span>
                </a>
            </div>

            @if($branches->isEmpty())
                <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-2xl mx-auto shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-obsidian text-amber-light mx-auto flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-display font-bold text-obsidian">Şube Listemiz Hazırlanıyor</h3>
                    <p class="text-sm text-obsidian/70 mt-2 leading-relaxed">
                        Aktif şubelerimiz genel merkez yönetim panelinden tanımlandığında çalışma saatleri, yol tarifi ve canlı QR menü bağlantılarıyla burada yer alacaktır.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('public.franchise') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider hover:bg-roast transition">
                            Şehrinizde Franchise Açın →
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-7">
                    @foreach($branches as $branch)
                        @php $status = $branch->openStatus(); @endphp
                        <div class="rounded-3xl bg-white border border-obsidian/10 p-7 shadow-sm hover:shadow-lg transition flex flex-col justify-between space-y-6">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-sand text-obsidian">
                                        {{ $branch->city }}{{ $branch->district ? ' / ' . $branch->district : '' }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $status['is_open'] ? 'bg-sage/15 text-sage' : 'bg-terracotta/15 text-terracotta' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $status['is_open'] ? 'bg-sage' : 'bg-terracotta' }}"></span>
                                        {{ $status['status_text'] }}
                                    </span>
                                </div>

                                <h3 class="text-xl font-display font-bold text-obsidian">
                                    <a href="{{ route('public.branch.show', $branch->slug) }}" class="hover:text-amber-harvest transition">
                                        {{ $branch->name }}
                                    </a>
                                </h3>

                                <p class="text-xs text-obsidian/70 line-clamp-2">{{ $branch->address }}</p>
                                <p class="text-xs font-semibold text-obsidian/80">Bugün: {{ $status['hours_text'] }}</p>
                            </div>

                            <div class="pt-4 border-t border-obsidian/10 grid grid-cols-2 gap-3">
                                <a href="{{ route('public.branch.show', $branch->slug) }}"
                                   class="py-2.5 px-4 rounded-xl border border-obsidian/20 text-center text-xs font-bold text-obsidian hover:bg-sand transition">
                                    Şube Detayı
                                </a>
                                <a href="{{ route('qrmenu.show', $branch->slug) }}"
                                   class="py-2.5 px-4 rounded-xl bg-obsidian text-cream text-center text-xs font-bold hover:bg-amber-harvest transition">
                                    QR Menü
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <!-- SECTION 5: FRANCHISE CTA BANNER -->
    <section class="py-20 bg-obsidian text-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-charcoal border border-white/15 p-8 sm:p-14 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">FRANCHISE &amp; BÜYÜME EKOSİSTEMİ</span>
                    <h2 class="text-3xl sm:text-4xl font-display font-bold text-white">
                        {{ $siteSettings['franchise_title'] ?? 'Soohouse Coffee® Ailesine Yatırımcı Olarak Katılın' }}
                    </h2>
                    <p class="text-sm sm:text-base text-cream/75 leading-relaxed max-w-2xl">
                        {{ $siteSettings['franchise_subtitle'] ?? 'Lokasyon analizinden mimari projelendirmeye, ekipman tedarikinden barista akademisine ve merkezi dijital menü/fiyat yönetimine kadar tüm aşamalarda yanınızdayız.' }}
                    </p>
                </div>
                <div class="lg:col-span-4 flex lg:justify-end">
                    <a href="{{ route('public.franchise') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-full bg-amber-harvest text-white text-sm font-bold uppercase tracking-wider hover:bg-amber-light hover:text-obsidian transition shadow-xl">
                        <span>Franchise Başvurusu Yap</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
