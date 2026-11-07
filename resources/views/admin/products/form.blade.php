@extends('layouts.admin', [
    'title' => $product->exists ? $product->name . ' — Ürün Düzenle' : 'Merkez Havuzuna Yeni Ürün Ekle',
    'subtitle' => 'Merkez fiyatı, şube alt/üst fiyat sınırları, etiketler, sıcak/soğuk seçenekleri ve çekirdek bilgilerini tanımlayın.',
])

@section('content')
    <form method="POST"
          action="{{ $product->exists ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
          enctype="multipart/form-data"
          class="max-w-4xl space-y-6">
        @csrf
        @if($product->exists)
            @method('PUT')
        @endif

        <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Kategori *</label>
                    <select name="category_id" required class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm bg-white">
                        <option value="">Seçiniz...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Ürün Kodu (SKU) *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required placeholder="SH-ESP-001"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm font-mono uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Sıra No</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 1) }}"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Ürün Adı *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
            </div>

            <!-- Pricing & Branch Limits -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-cream border border-obsidian/10">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian mb-1">Merkez Varsayılan Fiyatı (₺) *</label>
                    <input type="number" step="0.01" name="base_price" value="{{ old('base_price', $product->base_price) }}" required
                           class="w-full rounded-xl border border-obsidian/20 bg-white px-3.5 py-2.5 text-sm font-bold">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/70 mb-1">Şube Min. Fiyat Sınırı (₺)</label>
                    <input type="number" step="0.01" name="min_price" value="{{ old('min_price', $product->min_price) }}" placeholder="Opsiyonel"
                           class="w-full rounded-xl border border-obsidian/20 bg-white px-3.5 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/70 mb-1">Şube Maks. Fiyat Sınırı (₺)</label>
                    <input type="number" step="0.01" name="max_price" value="{{ old('max_price', $product->max_price) }}" placeholder="Opsiyonel"
                           class="w-full rounded-xl border border-obsidian/20 bg-white px-3.5 py-2.5 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Kısa Açıklama (QR Menü Özeti)</label>
                    <textarea name="short_description" rows="2" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">{{ old('short_description', $product->short_description) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Detaylı Açıklama</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            <!-- Bean & Origin Metadata -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Çekirdek Kökeni (Opsiyonel)</label>
                    <input type="text" name="bean_origin" value="{{ old('bean_origin', $product->bean_origin) }}" placeholder="Örn: Kolombiya Supremo"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Kavrum Derecesi</label>
                    <input type="text" name="roast_level" value="{{ old('roast_level', $product->roast_level) }}" placeholder="Örn: Orta Kavrum"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Tadım Notları</label>
                    <input type="text" name="tasting_notes" value="{{ old('tasting_notes', $product->tasting_notes) }}" placeholder="Örn: Karamel, Kakao, Narenciye"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                </div>
            </div>

            <!-- Tags & Temperatures -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-2">Etiketler</label>
                    <div class="flex flex-wrap gap-2">
                        @php $currentTags = old('tags', $product->tags ?? []); @endphp
                        @foreach(\App\Models\Product::availableTags() as $tagKey => $tagLabel)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cream border border-obsidian/15 text-xs font-semibold cursor-pointer">
                                <input type="checkbox" name="tags[]" value="{{ $tagKey }}" @checked(in_array($tagKey, $currentTags))>
                                <span>{{ $tagLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-2">Sıcak / Soğuk Seçenekleri</label>
                    <div class="flex flex-wrap gap-3">
                        @php $currentTemps = old('temperature_options', $product->temperature_options ?? ['hot']); @endphp
                        @foreach(\App\Models\Product::availableTemperatures() as $tempKey => $tempLabel)
                            <label class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-cream border border-obsidian/15 text-xs font-bold cursor-pointer">
                                <input type="checkbox" name="temperature_options[]" value="{{ $tempKey }}" @checked(in_array($tempKey, $currentTemps))>
                                <span>{{ $tempLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Boyut / Varyantlar (JSON formatı, opsiyonel)</label>
                <input type="text" name="variants_json"
                       value="{{ old('variants_json', !empty($product->variants) ? json_encode($product->variants, JSON_UNESCAPED_UNICODE) : '') }}"
                       placeholder='[{"name":"Small","price_diff":0},{"name":"Large","price_diff":20}]'
                       class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs font-mono">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center pt-2">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Ürün Görseli</label>
                    <input type="file" name="image_file" accept="image/*" class="text-xs">
                </div>
                <div class="flex flex-wrap gap-4 pt-4">
                    <label class="inline-flex items-center gap-2 text-xs font-bold cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))>
                        <span>Havuzda Aktif</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs font-bold cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured))>
                        <span>Ana Sayfada Öne Çıkar</span>
                    </label>
                    @if(!$product->exists)
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-amber-harvest cursor-pointer">
                            <input type="checkbox" name="attach_to_all_branches" value="1" checked>
                            <span>Tüm Şube Menülerine Otomatik Ekle</span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="pt-4 border-t border-obsidian/10 flex items-center gap-3">
                <button type="submit" class="px-7 py-3.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                    {{ $product->exists ? 'Ürünü Güncelle' : 'Ürünü Havuza Kaydet' }}
                </button>
                <a href="{{ route('admin.products.index') }}" class="px-5 py-3.5 rounded-xl border border-obsidian/20 text-xs font-bold text-obsidian">İptal</a>
            </div>
        </div>
    </form>
@endsection
