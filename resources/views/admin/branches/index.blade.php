@extends('layouts.admin', [
    'title' => 'Merkez Şube Yönetimi',
    'subtitle' => 'Yeni şube eklendiğinde /subelerimiz/{slug} ve /qrmenu/{slug} sayfaları otomatik oluşturulur.',
])

@section('header_actions')
    <a href="{{ route('admin.branches.create') }}"
       class="px-5 py-2.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
        + Yeni Şube Ekle
    </a>
@endsection

@section('content')
    @if($branches->isEmpty())
        <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-xl mx-auto space-y-4">
            <h2 class="text-xl font-display font-bold text-obsidian">Henüz Şube Eklenmedi</h2>
            <p class="text-xs text-obsidian/65 leading-relaxed">
                İlk şubenizi ekleyerek şube detay sayfasını, harita konumunu, çalışma saatlerini ve dijital QR menüsünü hemen yayına alabilirsiniz.
            </p>
            <a href="{{ route('admin.branches.create') }}" class="inline-block px-6 py-3 rounded-xl bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider">
                + İlk Şubeyi Oluştur
            </a>
        </div>
    @else
        <div class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-2xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-sand/60 border-b border-obsidian/10 text-xs font-bold uppercase tracking-wider text-obsidian/70">
                            <th class="py-3.5 px-5">Şube Adı &amp; Slug</th>
                            <th class="py-3.5 px-4">İl / İlçe</th>
                            <th class="py-3.5 px-4">Masa Sayısı</th>
                            <th class="py-3.5 px-4">Durum</th>
                            <th class="py-3.5 px-4">Canlı Bağlantılar</th>
                            <th class="py-3.5 px-5 text-right">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-obsidian/10">
                        @foreach($branches as $branch)
                            <tr class="hover:bg-cream/50">
                                <td class="py-4 px-5">
                                    <p class="font-display font-bold text-obsidian">{{ $branch->name }}</p>
                                    <p class="text-xs text-obsidian/50 font-mono">/subelerimiz/{{ $branch->slug }}</p>
                                </td>
                                <td class="py-4 px-4 text-xs font-medium">
                                    {{ $branch->city }}{{ $branch->district ? ' / ' . $branch->district : '' }}
                                </td>
                                <td class="py-4 px-4 text-xs font-bold">
                                    {{ $branch->dining_tables_count }} masa
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $branch->is_active ? 'bg-sage/15 text-sage' : 'bg-terracotta/15 text-terracotta' }}">
                                        {{ $branch->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-xs space-x-2">
                                    <a href="{{ route('public.branch.show', $branch->slug) }}" target="_blank" class="text-obsidian font-semibold hover:underline">Sayfa ↗</a>
                                    <a href="{{ route('qrmenu.show', $branch->slug) }}" target="_blank" class="text-amber-harvest font-bold hover:underline">QR Menü ↗</a>
                                </td>
                                <td class="py-4 px-5 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('admin.branch.menu', $branch->slug) }}" class="px-3 py-1.5 rounded-lg bg-sand text-obsidian text-xs font-bold hover:bg-obsidian hover:text-cream transition">
                                        Menü &amp; Fiyat
                                    </a>
                                    <a href="{{ route('admin.branch.tables', $branch->slug) }}" class="px-3 py-1.5 rounded-lg bg-sand text-obsidian text-xs font-bold hover:bg-obsidian hover:text-cream transition">
                                        Masalar
                                    </a>
                                    <a href="{{ route('admin.branches.edit', $branch->slug) }}" class="px-3 py-1.5 rounded-lg bg-obsidian text-cream text-xs font-bold hover:bg-amber-harvest transition">
                                        Düzenle
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
