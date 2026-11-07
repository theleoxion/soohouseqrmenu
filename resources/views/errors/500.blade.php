@extends('layouts.public', [
    'metaTitle' => 'Geçici Sunucu Hatası (500) | Soohouse Coffee®',
    'noindex' => true,
])

@section('content')
    <section class="py-24 bg-cream">
        <div class="max-w-xl mx-auto px-4 text-center space-y-6">
            <div class="w-20 h-20 rounded-3xl bg-obsidian text-cream mx-auto flex items-center justify-center p-4">
                <img src="{{ asset('brand/soohouse-icon.svg') }}" alt="Soohouse" class="w-full h-full object-contain invert">
            </div>
            <span class="inline-block px-3.5 py-1 rounded-full bg-terracotta/15 text-terracotta text-xs font-bold uppercase tracking-widest">
                500 — SİSTEM BİLDİRİMİ
            </span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold text-obsidian">
                İsteğiniz İşlenirken Geçici Bir Durum Oluştu
            </h1>
            <p class="text-sm text-obsidian/70 leading-relaxed">
                Teknik ekibimiz durumdan haberdar edildi. Lütfen birkaç saniye sonra sayfayı yenileyin veya ana sayfaya dönün.
            </p>
            <div class="pt-2">
                <a href="{{ route('public.home') }}" class="inline-block px-6 py-3.5 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
                    Ana Sayfaya Dön
                </a>
            </div>
        </div>
    </section>
@endsection
