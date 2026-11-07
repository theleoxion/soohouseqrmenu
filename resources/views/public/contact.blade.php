@extends('layouts.public', [
    'breadcrumbs' => [['name' => 'İletişim', 'url' => route('public.contact')]],
])

@section('content')
    <section class="bg-obsidian text-cream py-16 sm:py-20 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">KURUMSAL İLETİŞİM</span>
            <h1 class="text-3xl sm:text-5xl font-display font-extrabold text-white mt-2">Genel Merkez İletişim</h1>
            <p class="text-sm sm:text-base text-cream/75 mt-3 max-w-2xl">
                Soohouse Coffee® genel merkez iletişim kanalları, kurumsal iş birlikleri ve şube iletişim listemiz.
            </p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10">
            <!-- Headquarters Info -->
            <div class="lg:col-span-6 space-y-6">
                <div class="rounded-3xl bg-white border border-obsidian/10 p-8 sm:p-10 space-y-6 shadow-sm">
                    <div class="flex items-center gap-4 border-b border-obsidian/10 pb-6">
                        <div class="w-14 h-14 rounded-2xl bg-obsidian p-2.5 flex items-center justify-center shrink-0">
                            <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-full h-full object-contain invert">
                        </div>
                        <div>
                            <h2 class="text-xl font-display font-bold text-obsidian">Soohouse Coffee® Genel Merkez</h2>
                            <p class="text-xs text-obsidian/60">Merkezi Yönetim ve Tedarik Koordinasyonu</p>
                        </div>
                    </div>

                    @if(empty($siteSettings['hq_address']) && empty($siteSettings['hq_phone']) && empty($siteSettings['hq_email']))
                        <div class="p-6 rounded-2xl bg-sand/60 border border-obsidian/10 text-sm text-obsidian/75 leading-relaxed">
                            Genel merkez açık adres ve doğrudan telefon bilgileri yönetim panelinden (<code class="text-xs font-bold">Site &amp; İçerik Yönetimi</code>) girildiğinde burada görüntülenecektir. Yatırımcı başvurularınız için <a href="{{ route('public.franchise') }}" class="text-amber-harvest font-bold underline">Franchise Başvuru Formunu</a> kullanabilirsiniz.
                        </div>
                    @else
                        <div class="space-y-4 text-sm">
                            @if(!empty($siteSettings['hq_address']))
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-obsidian/50 mb-1">Genel Merkez Adresi</span>
                                    <p class="text-obsidian font-medium leading-relaxed">{{ $siteSettings['hq_address'] }}</p>
                                </div>
                            @endif

                            @if(!empty($siteSettings['hq_phone']))
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-obsidian/50 mb-1">Telefon</span>
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSettings['hq_phone']) }}" class="text-lg font-display font-bold text-obsidian hover:text-amber-harvest">
                                        {{ $siteSettings['hq_phone'] }}
                                    </a>
                                </div>
                            @endif

                            @if(!empty($siteSettings['hq_email']))
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-obsidian/50 mb-1">Kurumsal E-posta</span>
                                    <a href="mailto:{{ $siteSettings['hq_email'] }}" class="font-bold text-amber-harvest hover:underline">
                                        {{ $siteSettings['hq_email'] }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Cards -->
            <div class="lg:col-span-6 space-y-6">
                <div class="rounded-3xl bg-obsidian text-cream p-8 sm:p-10 space-y-4">
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">FRANCHISE &amp; YATIRIM</span>
                    <h2 class="text-2xl font-display font-bold text-white">Yeni Şube Yatırımı İçin Başvurun</h2>
                    <p class="text-sm text-cream/75 leading-relaxed">
                        Soohouse Coffee® franchise koşulları, mimari konsept ve lokasyon değerlendirmesi için online başvuru formumuzu doldurabilirsiniz.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('public.franchise') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider hover:bg-amber-light hover:text-obsidian transition">
                            Franchise Başvuru Formu →
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl bg-white border border-obsidian/10 p-8 sm:p-10 space-y-4 shadow-sm">
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">MÜŞTERİ DENEYİMİ &amp; GERİ BİLDİRİM</span>
                    <h2 class="text-2xl font-display font-bold text-obsidian">Şube İstek, Öneri ve Şikayetleri</h2>
                    <p class="text-sm text-obsidian/70 leading-relaxed">
                        Şubelerimizdeki deneyiminizle ilgili istek, öneri ve şikayetlerinizi doğrudan ilgili şubenin dijital QR menüsü içerisindeki geri bildirim butonundan bize anında iletebilirsiniz.
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('public.branches') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-obsidian/20 text-obsidian text-xs font-bold uppercase tracking-wider hover:bg-sand transition">
                            Şubelerimizi Görüntüle →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
