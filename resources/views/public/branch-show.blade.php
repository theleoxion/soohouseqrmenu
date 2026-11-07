@extends('layouts.public', [
    'metaTitle' => ($branch->seo_title ?: $branch->name . ' Şubesi | Soohouse Coffee®'),
    'metaDescription' => ($branch->seo_description ?: $branch->name . ' adres, çalışma saatleri, yol tarifi, telefon ve güncel dijital QR menüsü.'),
    'breadcrumbs' => [
        ['name' => 'Şubelerimiz', 'url' => route('public.branches')],
        ['name' => $branch->name, 'url' => route('public.branch.show', $branch->slug)],
    ],
])

@push('structured_data')
    @php
        $dayMap = [
            1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
            4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
        ];
        $openingSpecs = [];
        foreach ($branch->hours as $h) {
            if (!$h->is_closed) {
                $openingSpecs[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => $dayMap[$h->day_of_week] ?? 'Monday',
                    'opens' => substr($h->open_time, 0, 5),
                    'closes' => substr($h->close_time, 0, 5),
                ];
            }
        }
        $localBusinessLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CafeOrCoffeeShop',
            'name' => 'Soohouse Coffee® - ' . $branch->name,
            'url' => route('public.branch.show', $branch->slug),
            'hasMenu' => route('qrmenu.show', $branch->slug),
            'image' => $branch->cover_image ? asset($branch->cover_image) : asset('brand/soohouse-logo-1200.png'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $branch->address,
                'addressLocality' => $branch->district ?: $branch->city,
                'addressRegion' => $branch->city,
                'addressCountry' => 'TR',
            ],
        ];
        if ($branch->phone) {
            $localBusinessLd['telephone'] = $branch->phone;
        }
        if ($branch->latitude && $branch->longitude) {
            $localBusinessLd['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
            ];
        }
        if (!empty($openingSpecs)) {
            $localBusinessLd['openingHoursSpecification'] = $openingSpecs;
        }
    @endphp
    <script type="application/ld+json">{!! json_encode($localBusinessLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    <!-- Branch Hero -->
    <section class="bg-obsidian text-cream py-16 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                <div class="space-y-4 max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="px-3 py-1 rounded-full bg-white/10 text-amber-light text-xs font-bold uppercase tracking-wider">
                            {{ $branch->city }}{{ $branch->district ? ' / ' . $branch->district : '' }}
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold {{ $openStatus['is_open'] ? 'bg-sage text-white' : 'bg-terracotta text-white' }}">
                            <span class="w-2 h-2 rounded-full bg-white"></span>
                            {{ $openStatus['status_text'] }} ({{ $openStatus['hours_text'] }})
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-display font-extrabold text-white">{{ $branch->name }}</h1>
                    <p class="text-sm sm:text-base text-cream/80 leading-relaxed">{{ $branch->address }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('qrmenu.show', $branch->slug) }}"
                       class="inline-flex items-center gap-3 px-7 py-4 rounded-full bg-amber-harvest text-white text-sm font-bold uppercase tracking-wider hover:bg-amber-light hover:text-obsidian transition shadow-lg">
                        <span>Şube Dijital QR Menüsü</span>
                        <span>→</span>
                    </a>
                    @if($branch->google_maps_url || ($branch->latitude && $branch->longitude))
                        <a href="{{ $branch->google_maps_url ?: 'https://www.google.com/maps/dir/?api=1&destination=' . $branch->latitude . ',' . $branch->longitude }}"
                           target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-6 py-4 rounded-full bg-white/10 border border-white/20 text-cream text-xs font-bold uppercase tracking-wider hover:bg-white hover:text-obsidian transition">
                            Yol Tarifi Al ↗
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Left Info & Description -->
            <div class="lg:col-span-7 space-y-8">
                @if($branch->cover_image)
                    <div class="rounded-3xl overflow-hidden border border-obsidian/10 bg-sand aspect-16/9">
                        <img src="{{ asset($branch->cover_image) }}" alt="{{ $branch->name }}" class="w-full h-full object-cover">
                    </div>
                @endif

                @if($branch->description)
                    <div class="rounded-3xl bg-white border border-obsidian/10 p-8 space-y-3">
                        <h2 class="text-xl font-display font-bold text-obsidian">Şube Hakkında</h2>
                        <p class="text-sm text-obsidian/80 leading-relaxed whitespace-pre-line">{{ $branch->description }}</p>
                    </div>
                @endif

                <!-- Map Embed -->
                @if($branch->latitude && $branch->longitude)
                    <div class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden">
                        <div class="px-6 py-4 border-b border-obsidian/10 flex items-center justify-between">
                            <h2 class="text-base font-display font-bold text-obsidian">Harita Konumu</h2>
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $branch->latitude }},{{ $branch->longitude }}"
                               target="_blank" rel="noopener"
                               class="text-xs font-bold text-amber-harvest hover:underline">
                                Google Haritalar'da Aç ↗
                            </a>
                        </div>
                        <div class="h-80 w-full bg-sand">
                            <iframe title="{{ $branch->name }} Harita"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $branch->longitude - 0.008 }}%2C{{ $branch->latitude - 0.008 }}%2C{{ $branch->longitude + 0.008 }}%2C{{ $branch->latitude + 0.008 }}&layer=mapnik&marker={{ $branch->latitude }}%2C{{ $branch->longitude }}"
                                    class="w-full h-full border-0"
                                    loading="lazy"></iframe>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Sidebar: Weekly Working Hours & Direct Contact -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Contact Card -->
                <div class="rounded-3xl bg-white border border-obsidian/10 p-8 space-y-5 shadow-sm">
                    <h2 class="text-xl font-display font-bold text-obsidian">İletişim &amp; Hızlı Erişim</h2>
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="block text-xs font-bold uppercase tracking-wider text-obsidian/50">Adres</span>
                            <span class="font-medium text-obsidian">{{ $branch->address }}</span>
                        </div>
                        @if($branch->phone)
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-obsidian/50">Telefon</span>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $branch->phone) }}" class="font-bold text-obsidian hover:text-amber-harvest">
                                    {{ $branch->phone }}
                                </a>
                            </div>
                        @endif
                        @if($branch->email)
                            <div>
                                <span class="block text-xs font-bold uppercase tracking-wider text-obsidian/50">E-posta</span>
                                <a href="mailto:{{ $branch->email }}" class="font-medium text-obsidian hover:text-amber-harvest">
                                    {{ $branch->email }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-obsidian/10 flex flex-wrap gap-3">
                        @if($branch->phone)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $branch->phone) }}"
                               class="flex-1 py-3 px-4 rounded-xl bg-obsidian text-cream text-center text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
                                Şubeyi Ara
                            </a>
                        @endif
                        @if($branch->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $branch->whatsapp) }}"
                               target="_blank" rel="noopener"
                               class="flex-1 py-3 px-4 rounded-xl bg-sage text-white text-center text-xs font-bold uppercase tracking-wider hover:opacity-90 transition">
                                WhatsApp
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Weekly Schedule Card -->
                <div class="rounded-3xl bg-white border border-obsidian/10 p-8 space-y-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-display font-bold text-obsidian">Haftalık Çalışma Saatleri</h2>
                        <span class="text-xs font-bold {{ $openStatus['is_open'] ? 'text-sage' : 'text-terracotta' }}">
                            {{ $openStatus['status_text'] }}
                        </span>
                    </div>

                    @if($branch->hours->isEmpty())
                        <p class="text-xs text-obsidian/60">Çalışma saatleri henüz belirtilmemiştir.</p>
                    @else
                        @php $todayIso = (int) now('Europe/Istanbul')->isoWeekday(); @endphp
                        <ul class="divide-y divide-obsidian/10 text-sm">
                            @foreach($branch->hours as $hour)
                                <li class="py-2.5 flex items-center justify-between {{ $hour->day_of_week === $todayIso ? 'font-bold text-amber-harvest' : 'text-obsidian/80' }}">
                                    <span>
                                        {{ \App\Models\BranchHour::dayName($hour->day_of_week) }}
                                        @if($hour->day_of_week === $todayIso)
                                            <span class="text-[10px] uppercase ml-1 px-1.5 py-0.5 rounded bg-amber-harvest/15 text-amber-harvest">Bugün</span>
                                        @endif
                                    </span>
                                    <span>
                                        @if($hour->is_closed)
                                            Kapalı
                                        @else
                                            {{ substr($hour->open_time, 0, 5) }} - {{ substr($hour->close_time, 0, 5) }}
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
