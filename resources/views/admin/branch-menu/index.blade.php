@extends('layouts.admin', [
    'title' => $branch->name . ' — Şube Menü, Fiyat ve İndirim Yönetimi',
    'subtitle' => 'Merkez ürün havuzundan şube menünüze ürün ekleyin/çıkarın, tükendi işaretleyin veya şubenize özel fiyat ve tarihli indirim tanımlayın.',
])

@section('header_actions')
    <a href="{{ route('qrmenu.show', $branch->slug) }}" target="_blank"
       class="px-4 py-2.5 rounded-xl bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider hover:bg-obsidian transition">
        Canlı QR Menüyü Aç ↗
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.branch.menu', $branch->slug) }}" class="rounded-2xl bg-white border border-obsidian/10 p-4 flex flex-wrap items-center gap-3">
            <select name="category_id" class="rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs font-semibold bg-white">
                <option value="">Tüm Kategoriler</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="SKU veya ürün adı ara..."
                   class="rounded-xl border border-obsidian/20 px-3.5 py-2 text-xs flex-1 min-w-48">
            <button type="submit" class="px-4 py-2 rounded-xl bg-obsidian text-cream text-xs font-bold cursor-pointer">Filtrele</button>
        </form>

        @if($products->isEmpty())
            <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-xl mx-auto space-y-3">
                <h2 class="text-xl font-display font-bold text-obsidian">Merkez Ürün Havuzunda Aktif Ürün Yok</h2>
                <p class="text-xs text-obsidian/65">Merkez tarafından ürün havuzuna ürünler eklendiğinde şube menünüze seçebilirsiniz.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($products as $product)
                    @php
                        /** @var \App\Models\BranchProduct|null $pivot */
                        $pivot = $product->getAttribute('pivot_record');
                        $calc = $product->getAttribute('calculated_price');
                        $isAvailable = $pivot ? (bool) $pivot->is_available : false;
                        $isSoldOut = $pivot ? (bool) $pivot->is_sold_out : false;
                    @endphp
                    <form method="POST"
                          action="{{ route('admin.branch.menu.update', [$branch->slug, $product->id]) }}"
                          class="rounded-2xl bg-white border border-obsidian/10 p-5 shadow-2xs space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-obsidian/10 pb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-mono font-bold text-amber-harvest">{{ $product->sku }}</span>
                                    <span class="text-xs text-obsidian/50">• {{ $product->category?->name }}</span>
                                </div>
                                <h3 class="text-base font-display font-bold text-obsidian">{{ $product->name }}</h3>
                                <p class="text-xs text-obsidian/55">
                                    Merkez Fiyatı: <strong>{{ \App\Services\PriceCalculationService::formatCurrency((float)$product->base_price) }}</strong>
                                    @if($product->min_price !== null || $product->max_price !== null)
                                        | Merkez Sınırı:
                                        {{ $product->min_price !== null ? \App\Services\PriceCalculationService::formatCurrency((float)$product->min_price) : '0 ₺' }}
                                        -
                                        {{ $product->max_price !== null ? \App\Services\PriceCalculationService::formatCurrency((float)$product->max_price) : 'Sınırsız' }}
                                    @endif
                                </p>
                            </div>

                            <!-- Effective Customer Price Preview Badge -->
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-obsidian/50">Müşterinin Gördüğü Fiyat</span>
                                    @if($calc['has_discount'])
                                        <span class="text-xs line-through text-obsidian/45 mr-1">{{ $calc['formatted_original'] }}</span>
                                    @endif
                                    <span class="text-lg font-display font-extrabold text-sage">{{ $calc['formatted_final'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            <!-- Availability & Sold Out Toggles -->
                            <div class="md:col-span-3 flex flex-col gap-2">
                                <label class="inline-flex items-center gap-2 text-xs font-bold text-obsidian cursor-pointer">
                                    <input type="checkbox" name="is_available" value="1" @checked($isAvailable) class="w-4 h-4 rounded">
                                    <span>Şube Menüsünde Göster</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs font-bold text-terracotta cursor-pointer">
                                    <input type="checkbox" name="is_sold_out" value="1" @checked($isSoldOut) class="w-4 h-4 rounded">
                                    <span>Geçici "Tükendi" İşaretle</span>
                                </label>
                            </div>

                            <!-- Branch Custom Price Override -->
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold text-obsidian/75 mb-1">Şube Özel Fiyatı (₺)</label>
                                <input type="number" step="0.01" name="custom_price"
                                       value="{{ $pivot?->custom_price }}"
                                       placeholder="Merkez ({{ $product->base_price }})"
                                       class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs font-bold">
                            </div>

                            <!-- Discount Type & Value -->
                            <div class="md:col-span-3 grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-obsidian/75 mb-1">İndirim Türü</label>
                                    <select name="discount_type" class="w-full rounded-xl border border-obsidian/20 px-2.5 py-2 text-xs bg-white">
                                        <option value="">İndirim Yok</option>
                                        <option value="percentage" @selected($pivot?->discount_type === 'percentage')>Yüzde (%)</option>
                                        <option value="fixed" @selected($pivot?->discount_type === 'fixed')>Sabit Tutar (₺)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-obsidian/75 mb-1">İndirim Değeri</label>
                                    <input type="number" step="0.01" name="discount_value" value="{{ $pivot?->discount_value }}" placeholder="0"
                                           class="w-full rounded-xl border border-obsidian/20 px-2.5 py-2 text-xs">
                                </div>
                            </div>

                            <!-- Optional Discount Start/End Dates -->
                            <div class="md:col-span-2 grid grid-cols-2 gap-1.5">
                                <div>
                                    <label class="block text-[10px] font-bold text-obsidian/65 mb-1">Başlangıç</label>
                                    <input type="date" name="discount_starts_at" value="{{ $pivot?->discount_starts_at?->format('Y-m-d') }}"
                                           class="w-full rounded-lg border border-obsidian/20 px-2 py-1.5 text-[11px]">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-obsidian/65 mb-1">Bitiş</label>
                                    <input type="date" name="discount_ends_at" value="{{ $pivot?->discount_ends_at?->format('Y-m-d') }}"
                                           class="w-full rounded-lg border border-obsidian/20 px-2 py-1.5 text-[11px]">
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="md:col-span-2">
                                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-obsidian text-cream text-xs font-bold hover:bg-amber-harvest transition cursor-pointer">
                                    Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>
        @endif
    </div>
@endsection
