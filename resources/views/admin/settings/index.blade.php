@extends('layouts.admin', [
    'title' => 'Site, Tema, Kurumsal İçerik (CMS) ve SEO Yönetimi',
    'subtitle' => 'Logo, renkler, ana sayfa metinleri, Biz Kimiz, iletişim, Basında Biz ve her sayfa için SEO alanlarını kod değişikliği olmadan düzenleyin.',
])

@section('content')
    <div class="space-y-10" x-data="{ tab: 'cms' }">
        <!-- Tab Selector -->
        <div class="flex flex-wrap gap-2 border-b border-obsidian/10 pb-3">
            <button type="button" @click="tab = 'cms'"
                    :class="tab === 'cms' ? 'bg-obsidian text-cream' : 'bg-white text-obsidian'"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider border border-obsidian/10 cursor-pointer">
                1. Tema, Kurumsal Metinler &amp; İletişim
            </button>
            <button type="button" @click="tab = 'seo'"
                    :class="tab === 'seo' ? 'bg-obsidian text-cream' : 'bg-white text-obsidian'"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider border border-obsidian/10 cursor-pointer">
                2. Sayfa Bazlı SEO Yönetimi ({{ $seoPages->count() }})
            </button>
            <button type="button" @click="tab = 'press'"
                    :class="tab === 'press' ? 'bg-obsidian text-cream' : 'bg-white text-obsidian'"
                    class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider border border-obsidian/10 cursor-pointer">
                3. Basında Biz İçerikleri ({{ $pressItems->count() }})
            </button>
        </div>

        <!-- TAB 1: CMS & Theme Settings -->
        <form x-show="tab === 'cms'" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            @php
                $groupTitles = [
                    'general' => 'Genel Marka Bilgileri',
                    'theme' => 'Marka Logoları & Renk Paleti',
                    'home' => 'Ana Sayfa Bölümleri',
                    'about' => 'Hakkımızda (Biz Kimiz) İçeriği',
                    'franchise' => 'Franchise Sayfası & KVKK Metni',
                    'contact' => 'Genel Merkez İletişim Bilgileri',
                    'social' => 'Sosyal Medya Bağlantıları',
                ];
            @endphp

            @foreach($groupTitles as $grpKey => $grpTitle)
                @if($settings->has($grpKey))
                    <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-5">
                        <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">{{ $grpTitle }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            @foreach($settings->get($grpKey) as $setting)
                                <div class="{{ $setting->type === 'textarea' ? 'md:col-span-2' : '' }}">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">
                                        {{ $setting->label ?: $setting->key }}
                                    </label>
                                    @if($setting->type === 'textarea')
                                        <textarea name="settings[{{ $setting->key }}]" rows="3"
                                                  class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">{{ $setting->value }}</textarea>
                                    @elseif($setting->type === 'color')
                                        <div class="flex items-center gap-3">
                                            <input type="color" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="h-10 w-16 rounded border">
                                            <input type="text" value="{{ $setting->value }}" readonly class="rounded-xl border border-obsidian/20 px-3 py-2 text-xs font-mono w-32 bg-cream">
                                        </div>
                                    @elseif($setting->type === 'image')
                                        <div class="space-y-2">
                                            <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs font-mono">
                                            <input type="file" name="files[{{ $setting->key }}]" accept="image/*,.ico,.svg" class="text-xs">
                                        </div>
                                    @else
                                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                               class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach

            <button type="submit" class="px-8 py-4 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                Tüm Site ve İçerik Ayarlarını Kaydet
            </button>
        </form>

        <!-- TAB 2: Page-by-Page SEO Management -->
        <div x-show="tab === 'seo'" x-cloak class="space-y-6">
            @foreach($seoPages as $seoPage)
                <form method="POST" action="{{ route('admin.settings.seo.update', $seoPage->id) }}" enctype="multipart/form-data"
                      class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between border-b border-obsidian/10 pb-3">
                        <h3 class="text-base font-display font-bold text-obsidian">{{ $seoPage->page_label }}</h3>
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-terracotta cursor-pointer">
                            <input type="checkbox" name="noindex" value="1" @checked($seoPage->noindex)>
                            <span>Noindex (Arama motorlarında gizle)</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Meta Title (Başlık)</label>
                            <input type="text" name="meta_title" value="{{ $seoPage->meta_title }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Open Graph Title (Opsiyonel)</label>
                            <input type="text" name="og_title" value="{{ $seoPage->og_title }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Meta Description (Açıklama)</label>
                            <textarea name="meta_description" rows="2" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">{{ $seoPage->meta_description }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">OG Görseli Yükle</label>
                            <input type="file" name="og_image_file" accept="image/*" class="text-xs">
                            @if($seoPage->og_image)
                                <p class="text-[11px] text-obsidian/50 mt-1 font-mono">Mevcut: {{ $seoPage->og_image }}</p>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-obsidian text-cream text-xs font-bold hover:bg-amber-harvest transition cursor-pointer">
                        {{ $seoPage->page_label }} SEO Kaydet
                    </button>
                </form>
            @endforeach
        </div>

        <!-- TAB 3: Press Items (/basinda-biz) -->
        <div x-show="tab === 'press'" x-cloak class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-5">
                <form method="POST" action="{{ route('admin.settings.press.store') }}" enctype="multipart/form-data"
                      class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                    @csrf
                    <h3 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Yeni Basın Haberi Ekle</h3>

                    <div>
                        <label class="block text-xs font-bold text-obsidian/75 mb-1">Başlık *</label>
                        <input type="text" name="title" required class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Yayın Kuruluşu</label>
                            <input type="text" name="publisher" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Yayın Tarihi</label>
                            <input type="date" name="published_at" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-obsidian/75 mb-1">Kısa Özet</label>
                        <textarea name="summary" rows="2" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-obsidian/75 mb-1">Dış Bağlantı (URL)</label>
                        <input type="url" name="external_url" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-obsidian/75 mb-1">Haber Görseli</label>
                        <input type="file" name="image_file" accept="image/*" class="text-xs">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider cursor-pointer">
                        + Basın İçeriği Ekle
                    </button>
                </form>
            </div>

            <div class="lg:col-span-7 space-y-3">
                @forelse($pressItems as $press)
                    <div class="p-4 rounded-2xl bg-white border border-obsidian/10 flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold text-amber-harvest">{{ $press->publisher }} • {{ $press->published_at?->format('d.m.Y') }}</p>
                            <h4 class="font-display font-bold text-sm text-obsidian">{{ $press->title }}</h4>
                            <p class="text-xs text-obsidian/65">{{ $press->summary }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.settings.press.destroy', $press->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-terracotta/15 text-terracotta text-xs font-bold cursor-pointer">Sil</button>
                        </form>
                    </div>
                @empty
                    <div class="p-10 rounded-3xl bg-white border border-obsidian/10 text-center text-xs text-obsidian/60">
                        Henüz basın haberi eklenmedi.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
