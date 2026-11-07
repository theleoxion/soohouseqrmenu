@extends('layouts.public', [
    'breadcrumbs' => [['name' => 'Basında Biz', 'url' => route('public.press')]],
])

@section('content')
    <section class="bg-obsidian text-cream py-16 sm:py-20 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">MEDYA &amp; KURUMSAL BÜLTEN</span>
            <h1 class="text-3xl sm:text-5xl font-display font-extrabold text-white mt-2">Basında Biz</h1>
            <p class="text-sm sm:text-base text-cream/75 mt-3 max-w-2xl">
                Soohouse Coffee® hakkında basında çıkan haberler, röportajlar ve kurumsal duyurular.
            </p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($pressItems->isEmpty())
                <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-2xl mx-auto shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-sand mx-auto flex items-center justify-center mb-4">
                        <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-10 h-10 object-contain">
                    </div>
                    <h2 class="text-2xl font-display font-bold text-obsidian">Basın Bültenleri Hazırlanıyor</h2>
                    <p class="text-sm text-obsidian/70 mt-2 leading-relaxed">
                        Soohouse Coffee® kurumsal haberleri ve medya yansımaları yönetim panelinden eklendiğinde burada yayınlanacaktır.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($pressItems as $item)
                        <article class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                @if($item->image)
                                    <div class="aspect-16/9 bg-sand overflow-hidden">
                                        <img src="{{ asset($item->image) }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover">
                                    </div>
                                @endif
                                <div class="p-7 space-y-3">
                                    <div class="flex items-center justify-between text-xs text-amber-harvest font-bold uppercase">
                                        <span>{{ $item->publisher ?: 'Basın Bülteni' }}</span>
                                        @if($item->published_at)
                                            <span class="text-obsidian/50">{{ $item->published_at->format('d.m.Y') }}</span>
                                        @endif
                                    </div>
                                    <h2 class="text-xl font-display font-bold text-obsidian">{{ $item->title }}</h2>
                                    @if($item->summary)
                                        <p class="text-sm text-obsidian/75 leading-relaxed">{{ $item->summary }}</p>
                                    @endif
                                    @if($item->content)
                                        <p class="text-xs text-obsidian/65 leading-relaxed whitespace-pre-line">{{ $item->content }}</p>
                                    @endif
                                </div>
                            </div>
                            @if($item->external_url)
                                <div class="px-7 pb-6 pt-3 border-t border-obsidian/10">
                                    <a href="{{ $item->external_url }}" target="_blank" rel="noopener" class="text-xs font-bold text-amber-harvest hover:underline">
                                        Haberi Kaynağında Oku ↗
                                    </a>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
