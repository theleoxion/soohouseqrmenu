@extends('layouts.public', [
    'metaTitle' => 'Sayfa Bulunamadı (404) | Soohouse Coffee®',
    'noindex' => true,
])

@section('content')
    <section class="py-24 bg-cream">
        <div class="max-w-xl mx-auto px-4 text-center space-y-6">
            <div class="w-20 h-20 rounded-3xl bg-obsidian text-cream mx-auto flex items-center justify-center p-4">
                <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-full h-full object-contain invert">
            </div>
            <span class="inline-block px-3.5 py-1 rounded-full bg-amber-harvest/15 text-amber-harvest text-xs font-bold uppercase tracking-widest">
                404 — SAYFA BULUNAMADI
            </span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-obsidian">
                Aradığınız Sayfa Taşınmış veya Kaldırılmış Olabilir
            </h1>
            <p class="text-sm text-obsidian/70 leading-relaxed">
                Ana sayfaya dönebilir, şubelerimizi inceleyebilir veya dijital QR menülerimize göz atabilirsiniz.
            </p>
            <div class="flex flex-wrap justify-center gap-3 pt-2">
                <a href="{{ route('public.home') }}" class="px-6 py-3.5 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
                    Ana Sayfaya Dön
                </a>
                <a href="{{ route('public.branches') }}" class="px-6 py-3.5 rounded-full border border-obsidian/20 text-obsidian text-xs font-bold uppercase tracking-wider hover:bg-sand transition">
                    Şubelerimiz &amp; QR Menü
                </a>
            </div>
        </div>
    </section>
@endsection
