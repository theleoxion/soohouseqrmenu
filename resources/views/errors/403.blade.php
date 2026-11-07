@extends('layouts.public', [
    'metaTitle' => 'Yetkisiz Erişim (403) | Soohouse Coffee®',
    'noindex' => true,
])

@section('content')
    <section class="py-24 bg-cream">
        <div class="max-w-xl mx-auto px-4 text-center space-y-6">
            <span class="inline-block px-3.5 py-1 rounded-full bg-terracotta/15 text-terracotta text-xs font-bold uppercase tracking-widest">
                403 — ERİŞİM YETKİSİ YOK
            </span>
            <h1 class="text-3xl font-display font-extrabold text-obsidian">
                Bu Alana Erişim Yetkiniz Bulunmamaktadır
            </h1>
            <p class="text-sm text-obsidian/70 leading-relaxed">
                {{ $exception->getMessage() ?: 'Bu işlemi gerçekleştirmek için gerekli rol veya şube kapsamı yetkisine sahip değilsiniz.' }}
            </p>
            <div class="flex justify-center gap-3 pt-2">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="px-6 py-3 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-wider">
                        Yönetim Paneline Dön
                    </a>
                @else
                    <a href="{{ route('public.home') }}" class="px-6 py-3 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-wider">
                        Ana Sayfaya Dön
                    </a>
                @endauth
            </div>
        </div>
    </section>
@endsection
