@extends('layouts.public', [
    'breadcrumbs' => [['name' => 'Ürünlerimiz', 'url' => route('public.products')]],
])

@section('content')
    <section class="bg-obsidian text-cream py-16 sm:py-20 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">KAHVE ÇEKİRDEKLERİ &amp; LEZZET HAVUZU</span>
                <h1 class="text-3xl sm:text-5xl font-display font-extrabold text-white">Ürünlerimiz</h1>
                <p class="text-sm sm:text-base text-cream/75 leading-relaxed">
                    Soohouse Coffee® genel merkezi tarafından geliştirilen kahve çekirdeklerimiz, espresso bazlı içeceklerimiz, soğuk demlemelerimiz ve tamamlayıcı lezzetlerimiz.
                </p>
            </div>
            <div>
                <a href="{{ route('public.branches') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider hover:bg-amber-light hover:text-obsidian transition">
                    Şube Fiyatları İçin QR Menü Seçin →
                </a>
            </div>
        </div>
    </section>

    <section class="py-16 bg-cream" x-data="{ activeCat: 'all', search: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($categories->isEmpty() || $categories->pluck('products')->flatten()->isEmpty())
                <!-- Clean Empty State -->
                <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-2xl mx-auto shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-sand mx-auto flex items-center justify-center mb-4">
                        <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-10 h-10 object-contain">
                    </div>
                    <h2 class="text-2xl font-display font-bold text-obsidian">Ürün Havuzu Henüz Eklenmedi</h2>
                    <p class="text-sm text-obsidian/70 mt-2 leading-relaxed">
                        Merkez ürün havuzuna kahve çekirdekleri ve menü ürünleri eklendiğinde tüm kategoriler, tadım notları ve ürün özellikleri burada listelenecektir.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('public.branches') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-wider">
                            Şubelerimizi İnceleyin
                        </a>
                    </div>
                </div>
            @else
                <!-- Filter & Search Bar -->
                <div class="mb-10 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 md:pb-0">
                        <button type="button"
                                @click="activeCat = 'all'"
                                :class="activeCat === 'all' ? 'bg-obsidian text-cream' : 'bg-white text-obsidian hover:bg-sand'"
                                class="px-4 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider border border-obsidian/10 shrink-0 transition cursor-pointer">
                            Tümü
                        </button>
                        @foreach($categories as $category)
                            @if($category->products->isNotEmpty())
                                <button type="button"
                                        @click="activeCat = '{{ $category->slug }}'"
                                        :class="activeCat === '{{ $category->slug }}' ? 'bg-obsidian text-cream' : 'bg-white text-obsidian hover:bg-sand'"
                                        class="px-4 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider border border-obsidian/10 shrink-0 transition cursor-pointer">
                                    {{ $category->name }} ({{ $category->products->count() }})
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <div class="relative w-full md:w-72">
                        <input type="search"
                               x-model="search"
                               placeholder="Ürün veya tadım notu ara..."
                               class="w-full rounded-full bg-white border border-obsidian/15 px-4 py-2.5 text-sm text-obsidian placeholder:text-obsidian/45 focus:outline-none focus:border-amber-harvest">
                    </div>
                </div>

                <!-- Categories & Products -->
                <div class="space-y-14">
                    @foreach($categories as $category)
                        @if($category->products->isNotEmpty())
                            <div x-show="activeCat === 'all' || activeCat === '{{ $category->slug }}'" class="space-y-6">
                                <div class="border-b border-obsidian/10 pb-3 flex items-end justify-between">
                                    <div>
                                        <h2 class="text-2xl font-display font-bold text-obsidian">{{ $category->name }}</h2>
                                        @if($category->description)
                                            <p class="text-xs text-obsidian/65 mt-0.5">{{ $category->description }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($category->products as $product)
                                        @php
                                            $tagLabels = \App\Models\Product::availableTags();
                                            $searchBlob = mb_strtolower($product->name . ' ' . $product->short_description . ' ' . $product->bean_origin . ' ' . $product->tasting_notes, 'UTF-8');
                                        @endphp
                                        <article x-show="!search || '{{ addslashes($searchBlob) }}'.includes(search.toLowerCase())"
                                                 class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                                            @if($product->image)
                                                <div class="aspect-16/10 bg-sand overflow-hidden">
                                                    <img src="{{ asset($product->image) }}"
                                                         alt="{{ $product->name }}"
                                                         loading="lazy"
                                                         class="w-full h-full object-cover">
                                                </div>
                                            @endif

                                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                                <div class="space-y-2">
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        @foreach(($product->temperature_options ?? []) as $temp)
                                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-sand text-obsidian">
                                                                {{ $temp === 'hot' ? 'Sıcak' : 'Soğuk' }}
                                                            </span>
                                                        @endforeach
                                                        @foreach(($product->tags ?? []) as $tag)
                                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-harvest/15 text-amber-harvest">
                                                                {{ $tagLabels[$tag] ?? $tag }}
                                                            </span>
                                                        @endforeach
                                                    </div>

                                                    <h3 class="text-lg font-display font-bold text-obsidian">{{ $product->name }}</h3>

                                                    @if($product->short_description)
                                                        <p class="text-sm text-obsidian/75 leading-relaxed">{{ $product->short_description }}</p>
                                                    @endif

                                                    @if($product->description)
                                                        <p class="text-xs text-obsidian/60 leading-relaxed">{{ $product->description }}</p>
                                                    @endif
                                                </div>

                                                @if($product->bean_origin || $product->roast_level || $product->tasting_notes)
                                                    <div class="pt-3 border-t border-obsidian/10 text-xs space-y-1 text-obsidian/75">
                                                        @if($product->bean_origin)
                                                            <div><strong class="text-obsidian">Köken:</strong> {{ $product->bean_origin }}</div>
                                                        @endif
                                                        @if($product->roast_level)
                                                            <div><strong class="text-obsidian">Kavrum:</strong> {{ $product->roast_level }}</div>
                                                        @endif
                                                        @if($product->tasting_notes)
                                                            <div><strong class="text-obsidian">Tadım Notları:</strong> {{ $product->tasting_notes }}</div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
