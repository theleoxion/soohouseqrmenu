@extends('layouts.public', [
    'breadcrumbs' => [['name' => 'Biz Kimiz', 'url' => route('public.about')]],
])

@section('content')
    <!-- Hero Header -->
    <section class="bg-obsidian text-cream py-16 sm:py-24 border-b border-white/10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-5">
            <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 text-amber-light text-xs font-bold uppercase tracking-widest">
                SOOHOUSE COFFEE® HİKAYESİ
            </span>
            <h1 class="text-3xl sm:text-5xl font-display font-extrabold text-white">
                {{ $siteSettings['about_title'] ?? 'Topraktan İlham Alan Kahve Kültürü' }}
            </h1>
            <p class="text-base sm:text-lg text-cream/80 leading-relaxed max-w-3xl mx-auto">
                {{ $siteSettings['about_lead'] ?? 'Soohouse Coffee®, nitelikli kahveyi ulaşılabilir kılmak, üretim disipliniyle sürdürülebilir bir kafe deneyimi sunmak ve yatırımcılarıyla birlikte büyüyen güçlü bir franchise ağı kurmak amacıyla yola çıkmıştır.' }}
            </p>
        </div>
    </section>

    <!-- Story & Emblem Explanation -->
    <section class="py-20 bg-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-5">
                    <div class="rounded-3xl bg-white border border-obsidian/10 p-10 text-center shadow-sm space-y-6">
                        <img src="{{ asset('brand/soohouse-logo.svg') }}"
                             alt="Soohouse Coffee® Amblem"
                             width="280"
                             height="140"
                             class="w-60 mx-auto h-auto object-contain">
                        <div class="pt-6 border-t border-obsidian/10 text-left space-y-2">
                            <h2 class="text-sm font-bold uppercase tracking-wider text-amber-harvest">Neden Traktör Amblemi?</h2>
                            <p class="text-xs text-obsidian/75 leading-relaxed">
                                Kahve fincana ulaşmadan önce toprağın, yağmurun, güneşin ve tarımsal emeğin ürünüdür. Amblemimizdeki yalın traktör çizgisi, gösterişten uzak gerçek üretimi, doğallığı ve toprağa duyduğumuz bağlılığı temsil eder.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-6">
                    <h2 class="text-2xl sm:text-3xl font-display font-bold text-obsidian">
                        Nitelikli Çekirdekten Kurumsal Şube Deneyimine
                    </h2>
                    <div class="prose prose-stone max-w-none text-obsidian/80 space-y-4 leading-relaxed text-base">
                        @foreach(explode("\n\n", $siteSettings['about_content'] ?? '') as $paragraph)
                            @if(trim($paragraph) !== '')
                                <p>{{ trim($paragraph) }}</p>
                            @endif
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4">
                        <div class="p-6 rounded-2xl bg-white border border-obsidian/10">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-amber-harvest mb-2">Vizyonumuz</h3>
                            <p class="text-sm text-obsidian/80 leading-relaxed">
                                {{ $siteSettings['about_vision'] ?? 'Nitelikli kahve çekirdeklerini, özgün mimari konsept ve dijital operasyon gücüyle birleştirerek Türkiye genelinde referans gösterilen bir kafe zinciri olmak.' }}
                            </p>
                        </div>
                        <div class="p-6 rounded-2xl bg-white border border-obsidian/10">
                            <h3 class="text-xs font-bold uppercase tracking-widest text-amber-harvest mb-2">Misyonumuz</h3>
                            <p class="text-sm text-obsidian/80 leading-relaxed">
                                {{ $siteSettings['about_mission'] ?? 'Misafirlerimize her şubede tutarlı, taze ve nitelikli lezzetler sunarken; iş ortaklarımıza kârlı, denetlenebilir ve uçtan uca desteklenen bir franchise modeli sağlamak.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
