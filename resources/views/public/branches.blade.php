@extends('layouts.public', [
    'breadcrumbs' => [['name' => 'Şubelerimiz', 'url' => route('public.branches')]],
])

@section('content')
    <section class="bg-obsidian text-cream py-16 sm:py-20 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">ŞUBE AĞI &amp; CANLI DİJİTAL MENÜ</span>
                <h1 class="text-3xl sm:text-5xl font-display font-extrabold text-white">Şubelerimiz</h1>
                <p class="text-sm sm:text-base text-cream/75 leading-relaxed">
                    Size en yakın Soohouse Coffee® şubesinin anlık açık/kapalı durumunu, çalışma saatlerini, yol tarifini ve şubeye özel dijital QR menüsünü inceleyin.
                </p>
            </div>

            @if($cities->isNotEmpty())
                <form method="GET" action="{{ route('public.branches') }}" class="flex items-center gap-2">
                    <label for="sehir-select" class="text-xs font-semibold text-cream/70">Şehir:</label>
                    <select id="sehir-select" name="sehir" onchange="this.form.submit()"
                            class="rounded-full bg-charcoal border border-white/20 text-cream px-4 py-2.5 text-xs font-bold focus:outline-none focus:border-amber-harvest">
                        <option value="">Tüm Şehirler</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" @selected($cityFilter === $city)>{{ $city }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </section>

    <section class="py-16 bg-cream" x-data="{ selectedMapUrl: null, selectedBranchName: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            @if($branches->isEmpty())
                <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-2xl mx-auto shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-sand mx-auto flex items-center justify-center mb-4">
                        <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-10 h-10 object-contain">
                    </div>
                    <h2 class="text-2xl font-display font-bold text-obsidian">Henüz Aktif Şube Kaydı Bulunmuyor</h2>
                    <p class="text-sm text-obsidian/70 mt-2 leading-relaxed">
                        Yönetim panelinden yeni şube eklendiğinde şube kartları, harita konumları, çalışma saatleri ve QR menü bağlantıları otomatik olarak burada yayınlanacaktır.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('public.franchise') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider">
                            Franchise Başvurusu Yapın →
                        </a>
                    </div>
                </div>
            @else
                <!-- Interactive Map Overview -->
                @php
                    $firstWithCoords = $branches->first(fn($b) => $b->latitude && $b->longitude);
                @endphp
                @if($firstWithCoords)
                    <div class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-sm">
                        <div class="px-6 py-4 bg-obsidian text-cream flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-harvest"></span>
                                <span class="text-xs font-bold uppercase tracking-wider" x-text="selectedBranchName || '{{ addslashes($firstWithCoords->name) }} Harita Konumu'"></span>
                            </div>
                            <span class="text-xs text-cream/60">Şube kartlarındaki "Haritada Göster" butonuyla konumu değiştirebilirsiniz</span>
                        </div>
                        <div class="h-80 sm:h-96 w-full bg-sand">
                            <iframe title="Soohouse Coffee Şube Haritası"
                                    :src="selectedMapUrl || 'https://www.openstreetmap.org/export/embed.html?bbox={{ $firstWithCoords->longitude - 0.01 }}%2C{{ $firstWithCoords->latitude - 0.01 }}%2C{{ $firstWithCoords->longitude + 0.01 }}%2C{{ $firstWithCoords->latitude + 0.01 }}&layer=mapnik&marker={{ $firstWithCoords->latitude }}%2C{{ $firstWithCoords->longitude }}'"
                                    class="w-full h-full border-0"
                                    loading="lazy"></iframe>
                        </div>
                    </div>
                @endif

                <!-- Branch Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($branches as $branch)
                        @php $status = $branch->openStatus(); @endphp
                        <article class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col justify-between">
                            <div>
                                @if($branch->cover_image)
                                    <div class="aspect-16/9 bg-sand overflow-hidden">
                                        <img src="{{ asset($branch->cover_image) }}"
                                             alt="{{ $branch->name }}"
                                             loading="lazy"
                                             class="w-full h-full object-cover">
                                    </div>
                                @endif

                                <div class="p-7 space-y-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-sand text-obsidian">
                                            {{ $branch->city }}{{ $branch->district ? ' / ' . $branch->district : '' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $status['is_open'] ? 'bg-sage/15 text-sage' : 'bg-terracotta/15 text-terracotta' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $status['is_open'] ? 'bg-sage' : 'bg-terracotta' }}"></span>
                                            {{ $status['status_text'] }}
                                        </span>
                                    </div>

                                    <h2 class="text-2xl font-display font-bold text-obsidian">
                                        <a href="{{ route('public.branch.show', $branch->slug) }}" class="hover:text-amber-harvest transition">
                                            {{ $branch->name }}
                                        </a>
                                    </h2>

                                    <p class="text-sm text-obsidian/70 leading-relaxed">{{ $branch->address }}</p>

                                    <div class="pt-2 text-xs space-y-1.5 text-obsidian/80">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-obsidian/60">Bugünkü Saatler:</span>
                                            <span class="font-bold">{{ $status['hours_text'] }}</span>
                                        </div>
                                        @if($branch->phone)
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-obsidian/60">Telefon:</span>
                                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $branch->phone) }}" class="font-bold hover:text-amber-harvest">{{ $branch->phone }}</a>
                                            </div>
                                        @endif
                                    </div>

                                    @if($branch->latitude && $branch->longitude)
                                        <button type="button"
                                                @click="selectedMapUrl = 'https://www.openstreetmap.org/export/embed.html?bbox={{ $branch->longitude - 0.008 }}%2C{{ $branch->latitude - 0.008 }}%2C{{ $branch->longitude + 0.008 }}%2C{{ $branch->latitude + 0.008 }}&layer=mapnik&marker={{ $branch->latitude }}%2C{{ $branch->longitude }}'; selectedBranchName = '{{ addslashes($branch->name) }} Harita Konumu'; window.scrollTo({top: 220, behavior: 'smooth'})"
                                                class="text-xs font-bold text-amber-harvest hover:underline cursor-pointer">
                                            Haritada Göster ↑
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="px-7 pb-7 pt-4 border-t border-obsidian/10 grid grid-cols-2 gap-3">
                                <a href="{{ route('public.branch.show', $branch->slug) }}"
                                   class="py-3 px-4 rounded-xl border border-obsidian/20 text-center text-xs font-bold text-obsidian hover:bg-sand transition">
                                    Şube Sayfası
                                </a>
                                <a href="{{ route('qrmenu.show', $branch->slug) }}"
                                   class="py-3 px-4 rounded-xl bg-obsidian text-cream text-center text-xs font-bold hover:bg-amber-harvest transition">
                                    QR Menü Aç
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
