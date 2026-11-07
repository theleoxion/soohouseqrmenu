<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $brandName = $siteSettings['brand_name'] ?? 'Soohouse Coffee®';
        $canonicalUrl = route('qrmenu.show', $branch->slug);
        $title = $branch->name . ' Dijital QR Menü | ' . $brandName;
        $desc = $branch->name . ' güncel kahve ve lezzet menüsü, çalışma saatleri ve müşteri geri bildirim formu.';
    @endphp

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $desc }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @if($isTableSpecific)
        <meta name="robots" content="noindex, follow">
    @else
        <meta name="robots" content="index, follow">
    @endif

    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $desc }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ asset('brand/soohouse-logo-1200.png') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('soohouse-icon-180.png') }}">
    <meta name="theme-color" content="#0D0D0C">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(!$isTableSpecific && $categories->isNotEmpty())
        @php
            $menuSections = [];
            foreach ($categories as $cat) {
                $items = [];
                foreach ($cat->products as $prod) {
                    $calc = $prod->getAttribute('calculated_price');
                    $items[] = [
                        '@type' => 'MenuItem',
                        'name' => $prod->name,
                        'description' => $prod->short_description ?: $prod->description,
                        'offers' => [
                            '@type' => 'Offer',
                            'price' => number_format($calc['final_price'], 2, '.', ''),
                            'priceCurrency' => 'TRY',
                        ],
                    ];
                }
                $menuSections[] = [
                    '@type' => 'MenuSection',
                    'name' => $cat->name,
                    'hasMenuItem' => $items,
                ];
            }
            $menuJsonLd = [
                '@context' => 'https://schema.org',
                '@type' => 'Menu',
                'name' => $branch->name . ' QR Menü',
                'url' => $canonicalUrl,
                'hasMenuSection' => $menuSections,
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($menuJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
</head>
<body class="min-h-screen bg-cream text-obsidian antialiased pb-24"
      x-data="{
          search: '',
          activeCat: 'all',
          feedbackModalOpen: {{ $errors->any() ? 'true' : 'false' }},
          thankYouModalOpen: {{ session('feedback_thank_you') ? 'true' : 'false' }},
          infoModalOpen: false,
          selectedType: '{{ old('type', 'request') }}'
      }">

    <!-- Compact Brand & Branch Header -->
    <header class="bg-obsidian text-cream border-b border-white/10">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('public.home') }}" class="flex items-center gap-3">
                    <div class="bg-cream px-3 py-1.5 rounded-xl">
                        <img src="{{ asset('brand/soohouse-logo.svg') }}" alt="{{ $brandName }}" width="130" height="65" class="h-8 w-auto object-contain">
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="infoModalOpen = true"
                            class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-xs font-bold text-cream flex items-center gap-1.5 cursor-pointer">
                        <span>Şube Bilgisi</span>
                    </button>
                    <button type="button"
                            @click="feedbackModalOpen = true"
                            class="px-3.5 py-2 rounded-xl bg-amber-harvest text-white text-xs font-bold flex items-center gap-1.5 shadow-sm hover:bg-amber-light hover:text-obsidian transition cursor-pointer">
                        <span>İstek / Öneri / Şikayet</span>
                    </button>
                </div>
            </div>

            <!-- Branch & Table Status Pill Bar -->
            <div class="mt-4 pt-3 border-t border-white/10 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <h1 class="font-display font-bold text-sm sm:text-base text-white">{{ $branch->name }}</h1>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $openStatus['is_open'] ? 'bg-sage text-white' : 'bg-terracotta text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        {{ $openStatus['status_text'] }}
                    </span>
                </div>

                <div class="flex items-center gap-2 text-cream/80">
                    @if($table)
                        <span class="px-2.5 py-1 rounded-lg bg-amber-harvest/25 border border-amber-harvest text-amber-light font-bold">
                            Masa: {{ $table->name }} (#{{ $table->table_number }})
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-lg bg-white/10 text-cream/70 font-medium">
                            Masa: Belirtilmedi
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Sticky Search & Category Tabs -->
    <div class="sticky top-0 z-30 bg-cream/95 backdrop-blur-md border-b border-obsidian/10 shadow-xs">
        <div class="max-w-3xl mx-auto px-4 py-3 space-y-2.5">
            <!-- Instant Search Input -->
            <div class="relative">
                <input type="search"
                       x-model="search"
                       placeholder="Menüde kahve, içecek veya tatlı ara..."
                       aria-label="Menüde Ara"
                       class="w-full rounded-xl bg-white border border-obsidian/15 pl-10 pr-4 py-2.5 text-sm text-obsidian placeholder:text-obsidian/45 focus:outline-none focus:border-amber-harvest">
                <svg class="w-4 h-4 text-obsidian/40 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Horizontal Category Pills -->
            @if($categories->isNotEmpty())
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-0.5">
                    <button type="button"
                            @click="activeCat = 'all'; window.scrollTo({top: 0, behavior: 'smooth'})"
                            :class="activeCat === 'all' ? 'bg-obsidian text-cream' : 'bg-white text-obsidian border-obsidian/15'"
                            class="px-3.5 py-1.5 rounded-full text-xs font-bold border shrink-0 transition cursor-pointer">
                        Tümü
                    </button>
                    @foreach($categories as $category)
                        <button type="button"
                                @click="activeCat = '{{ $category->slug }}'"
                                :class="activeCat === '{{ $category->slug }}' ? 'bg-obsidian text-cream' : 'bg-white text-obsidian border-obsidian/15'"
                                class="px-3.5 py-1.5 rounded-full text-xs font-bold border shrink-0 transition cursor-pointer">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Menu Items Stream -->
    <main class="max-w-3xl mx-auto px-4 py-6 space-y-8">
        @if($categories->isEmpty())
            <div class="rounded-3xl bg-white border border-obsidian/10 p-10 text-center space-y-3 my-6">
                <div class="w-14 h-14 rounded-2xl bg-sand mx-auto flex items-center justify-center">
                    <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-9 h-9 object-contain">
                </div>
                <h2 class="text-lg font-display font-bold text-obsidian">Şube Menüsü Güncelleniyor</h2>
                <p class="text-xs text-obsidian/70 max-w-md mx-auto leading-relaxed">
                    {{ $branch->name }} şubemiz için aktif menü ürünleri tanımlandığında burada anlık olarak listelenecektir.
                </p>
            </div>
        @else
            @php $tagMap = \App\Models\Product::availableTags(); @endphp
            @foreach($categories as $category)
                <section x-show="activeCat === 'all' || activeCat === '{{ $category->slug }}'" class="space-y-3">
                    <div class="flex items-center justify-between border-b border-obsidian/10 pb-2">
                        <h2 class="text-lg font-display font-bold text-obsidian">{{ $category->name }}</h2>
                        <span class="text-[11px] font-semibold text-obsidian/50">{{ $category->products->count() }} ürün</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($category->products as $product)
                            @php
                                $calc = $product->getAttribute('calculated_price');
                                $isSoldOut = (bool) $product->getAttribute('is_sold_out');
                                $searchKey = mb_strtolower($product->name . ' ' . $product->short_description . ' ' . $product->description, 'UTF-8');
                            @endphp
                            <article x-show="!search || '{{ addslashes($searchKey) }}'.includes(search.toLowerCase())"
                                     class="rounded-2xl bg-white border border-obsidian/10 p-4 flex gap-4 items-start shadow-2xs {{ $isSoldOut ? 'opacity-65' : '' }}">
                                @if($product->image)
                                    <div class="w-22 h-22 sm:w-24 sm:h-24 rounded-xl bg-sand overflow-hidden shrink-0">
                                        <img src="{{ asset($product->image) }}"
                                             alt="{{ $product->name }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-sand/70 flex items-center justify-center shrink-0 p-4">
                                        <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="{{ $product->name }}" class="w-full h-full object-contain opacity-65">
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <h3 class="text-base font-display font-bold text-obsidian leading-snug">{{ $product->name }}</h3>
                                            <div class="flex flex-wrap items-center gap-1 mt-1">
                                                @if($isSoldOut)
                                                    <span class="px-2 py-0.5 rounded bg-terracotta text-white text-[10px] font-bold uppercase">
                                                        Tükendi
                                                    </span>
                                                @endif
                                                @if($calc['has_discount'])
                                                    <span class="px-2 py-0.5 rounded bg-sage text-white text-[10px] font-bold">
                                                        {{ $calc['discount_label'] }}
                                                    </span>
                                                @endif
                                                @foreach(($product->temperature_options ?? []) as $temp)
                                                    <span class="px-2 py-0.5 rounded bg-sand text-obsidian/80 text-[10px] font-semibold">
                                                        {{ $temp === 'hot' ? 'Sıcak' : 'Soğuk' }}
                                                    </span>
                                                @endforeach
                                                @foreach(($product->tags ?? []) as $tag)
                                                    <span class="px-2 py-0.5 rounded bg-amber-harvest/15 text-amber-harvest text-[10px] font-bold">
                                                        {{ $tagMap[$tag] ?? $tag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>

                                        <!-- Price Display with Strikethrough Discount -->
                                        <div class="text-right shrink-0">
                                            @if($calc['has_discount'])
                                                <span class="block text-xs text-obsidian/45 line-through font-medium tabular-nums">
                                                    {{ $calc['formatted_original'] }}
                                                </span>
                                            @endif
                                            <span class="block text-base sm:text-lg font-display font-extrabold text-obsidian tabular-nums">
                                                {{ $calc['formatted_final'] }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($product->short_description || $product->description)
                                        <p class="text-xs text-obsidian/70 mt-2 leading-relaxed">
                                            {{ $product->short_description ?: $product->description }}
                                        </p>
                                    @endif

                                    @if(!empty($product->variants))
                                        <div class="mt-2.5 pt-2 border-t border-obsidian/5 flex flex-wrap gap-2">
                                            @foreach($product->variants as $variant)
                                                @if(!empty($variant['name']))
                                                    <span class="px-2.5 py-1 rounded-lg bg-cream border border-obsidian/10 text-[11px] font-semibold text-obsidian">
                                                        {{ $variant['name'] }}
                                                        @if(!empty($variant['price_diff']) && (float)$variant['price_diff'] > 0)
                                                            (+{{ \App\Services\PriceCalculationService::formatCurrency((float)$variant['price_diff']) }})
                                                        @endif
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif

        <!-- Branch Contact & Quick Links Footer Card -->
        <section class="rounded-3xl bg-obsidian text-cream p-6 space-y-4 mt-10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-harvest">{{ $branch->name }}</p>
                    <p class="text-xs text-cream/75 mt-1">{{ $branch->address }}</p>
                </div>
                <span class="text-xs font-bold text-amber-light">{{ $openStatus['hours_text'] }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-2">
                @if($branch->phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $branch->phone) }}"
                       class="py-2.5 px-3 rounded-xl bg-white/10 hover:bg-white/20 text-center text-xs font-bold transition">
                        Şubeyi Ara
                    </a>
                @endif
                @if($branch->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $branch->whatsapp) }}"
                       target="_blank" rel="noopener"
                       class="py-2.5 px-3 rounded-xl bg-sage text-white text-center text-xs font-bold transition">
                        WhatsApp
                    </a>
                @endif
                @if($branch->google_maps_url || ($branch->latitude && $branch->longitude))
                    <a href="{{ $branch->google_maps_url ?: 'https://www.google.com/maps/dir/?api=1&destination=' . $branch->latitude . ',' . $branch->longitude }}"
                       target="_blank" rel="noopener"
                       class="py-2.5 px-3 rounded-xl bg-white/10 hover:bg-white/20 text-center text-xs font-bold transition">
                        Yol Tarifi ↗
                    </a>
                @endif
            </div>

            <div class="mt-8 pt-6 border-t border-white/10 text-center">
                <p class="text-[10px] text-cream/40 tracking-wide">Developed by <span class="text-cream/60 font-medium">Joyxen</span></p>
            </div>
        </section>
    </main>

    <!-- Floating Bottom Feedback Action Bar -->
    <div class="fixed bottom-4 inset-x-4 max-w-md mx-auto z-30">
        <button type="button"
                @click="feedbackModalOpen = true"
                class="w-full py-3.5 px-6 rounded-2xl bg-obsidian text-cream border border-amber-harvest/50 shadow-2xl flex items-center justify-between text-xs font-bold uppercase tracking-wider hover:bg-charcoal transition cursor-pointer">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-harvest"></span>
                <span>İstek / Öneri / Şikayet İlet</span>
            </span>
            <span class="text-amber-light">Formu Aç →</span>
        </button>
    </div>

    <!-- MODAL 1: İstek / Öneri / Şikayet Formu (Section 7 Strict Spec) -->
    <div x-show="feedbackModalOpen"
         x-cloak
         class="fixed inset-0 z-50 bg-obsidian/75 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="feedbackModalOpen = false"
             class="w-full max-w-lg rounded-t-3xl sm:rounded-3xl bg-cream text-obsidian p-6 sm:p-8 shadow-2xl border border-obsidian/10 space-y-5">
            <div class="flex items-center justify-between border-b border-obsidian/10 pb-4">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-amber-harvest">{{ $branch->name }}</span>
                    <h2 class="text-xl font-display font-bold text-obsidian">İstek, Öneri veya Şikayetiniz</h2>
                </div>
                <button type="button" @click="feedbackModalOpen = false" class="w-9 h-9 rounded-full bg-sand text-obsidian font-bold cursor-pointer">✕</button>
            </div>

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-terracotta/10 border border-terracotta/30 text-terracotta text-xs">
                    @foreach($errors->all() as $err)
                        <p>• {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('qrmenu.feedback.store', $branch->slug) }}" class="space-y-5">
                @csrf
                <!-- Hidden table token (Customer does not see or edit branch/table/date/time) -->
                <input type="hidden" name="table_token" value="{{ $table?->token }}">
                <div class="hidden" aria-hidden="true">
                    <input type="text" name="website_trap" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/70 mb-2">Bildirim Türünü Seçin *</label>
                    <div class="grid grid-cols-3 gap-2.5">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="request" x-model="selectedType" class="sr-only">
                            <div :class="selectedType === 'request' ? 'bg-obsidian text-cream border-obsidian' : 'bg-white text-obsidian border-obsidian/15'"
                                 class="py-3 px-3 rounded-xl border text-center text-xs font-bold transition">
                                İstek
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="suggestion" x-model="selectedType" class="sr-only">
                            <div :class="selectedType === 'suggestion' ? 'bg-obsidian text-cream border-obsidian' : 'bg-white text-obsidian border-obsidian/15'"
                                 class="py-3 px-3 rounded-xl border text-center text-xs font-bold transition">
                                Öneri
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="complaint" x-model="selectedType" class="sr-only">
                            <div :class="selectedType === 'complaint' ? 'bg-terracotta text-white border-terracotta' : 'bg-white text-obsidian border-obsidian/15'"
                                 class="py-3 px-3 rounded-xl border text-center text-xs font-bold transition">
                                Şikayet
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="feedback-message" class="block text-xs font-bold uppercase tracking-wider text-obsidian/70 mb-1.5">Mesajınız *</label>
                    <textarea id="feedback-message"
                              name="message"
                              rows="4"
                              required
                              minlength="5"
                              maxlength="1500"
                              placeholder="Deneyiminizi, isteğinizi veya önerinizi buraya yazabilirsiniz (Kişisel bilgi zorunlu değildir)..."
                              class="w-full rounded-2xl bg-white border border-obsidian/20 p-4 text-sm text-obsidian focus:outline-none focus:border-amber-harvest">{{ old('message') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full py-4 rounded-full bg-amber-harvest text-white text-xs font-bold uppercase tracking-widest hover:bg-obsidian transition cursor-pointer">
                    Gönder
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL 2: Thank You Screen After Submission -->
    <div x-show="thankYouModalOpen"
         x-cloak
         class="fixed inset-0 z-50 bg-obsidian/75 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-3xl bg-cream text-obsidian p-8 text-center space-y-4 shadow-2xl border border-obsidian/10">
            <div class="w-16 h-16 rounded-full bg-sage/15 text-sage mx-auto flex items-center justify-center text-2xl font-bold">✓</div>
            <h2 class="text-2xl font-display font-bold text-obsidian">Teşekkür Ederiz!</h2>
            <p class="text-sm text-obsidian/75 leading-relaxed">
                Mesajınız {{ $branch->name }} yönetimine ve Soohouse Coffee® kalite ekibine başarıyla iletilmiştir.
            </p>
            <button type="button"
                    @click="thankYouModalOpen = false"
                    class="w-full py-3.5 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-widest cursor-pointer">
                Menüye Dön
            </button>
        </div>
    </div>

    <!-- MODAL 3: Branch Hours & HQ Info -->
    <div x-show="infoModalOpen"
         x-cloak
         class="fixed inset-0 z-50 bg-obsidian/75 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div @click.away="infoModalOpen = false"
             class="w-full max-w-lg rounded-t-3xl sm:rounded-3xl bg-cream text-obsidian p-6 sm:p-8 shadow-2xl space-y-5 max-h-[85vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-obsidian/10 pb-4">
                <h2 class="text-xl font-display font-bold text-obsidian">{{ $branch->name }} Bilgileri</h2>
                <button type="button" @click="infoModalOpen = false" class="w-9 h-9 rounded-full bg-sand text-obsidian font-bold cursor-pointer">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <p><strong>Adres:</strong> {{ $branch->address }}</p>
                @if($branch->phone)
                    <p><strong>Telefon:</strong> <a href="tel:{{ $branch->phone }}" class="underline font-bold">{{ $branch->phone }}</a></p>
                @endif
            </div>

            @if($branch->hours->isNotEmpty())
                <div class="space-y-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-harvest">Çalışma Saatleri</h3>
                    <ul class="divide-y divide-obsidian/10 text-xs">
                        @foreach($branch->hours as $h)
                            <li class="py-1.5 flex justify-between">
                                <span>{{ \App\Models\BranchHour::dayName($h->day_of_week) }}</span>
                                <span class="font-semibold">{{ $h->is_closed ? 'Kapalı' : substr($h->open_time, 0, 5) . ' - ' . substr($h->close_time, 0, 5) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
