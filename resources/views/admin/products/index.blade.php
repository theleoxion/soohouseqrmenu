@extends('layouts.admin', [
    'title' => 'Merkez Ürün Havuzu',
    'subtitle' => 'Ürünler yalnızca genel merkezde oluşturulur. Şubeler bu havuzdan kendi menülerine ürün seçer.',
])

@section('header_actions')
    <a href="{{ route('admin.excel.index') }}" class="px-4 py-2.5 rounded-xl border border-obsidian/20 text-xs font-bold text-obsidian hover:bg-sand transition">
        Excel İçe / Dışa Aktar
    </a>
    <a href="{{ route('admin.products.create') }}" class="px-5 py-2.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition">
        + Yeni Ürün Ekle
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <!-- Filter Bar -->
        <form method="GET" action="{{ route('admin.products.index') }}" class="rounded-2xl bg-white border border-obsidian/10 p-4 flex flex-wrap items-center gap-3">
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
            <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center max-w-xl mx-auto space-y-4">
                <h2 class="text-xl font-display font-bold text-obsidian">Merkez Ürün Havuzu Boş</h2>
                <p class="text-xs text-obsidian/65 leading-relaxed">
                    Yeni ürün ekleyebilir veya Excel şablonunu kullanarak toplu ürün yüklemesi yapabilirsiniz.
                </p>
                <div class="flex justify-center gap-3">
                    <a href="{{ route('admin.products.create') }}" class="px-5 py-2.5 rounded-xl bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider">
                        + Ürün Ekle
                    </a>
                    <a href="{{ route('admin.excel.index') }}" class="px-5 py-2.5 rounded-xl border border-obsidian/20 text-obsidian text-xs font-bold uppercase tracking-wider">
                        Excel'den Yükle
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-3xl bg-white border border-obsidian/10 overflow-hidden shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-sand/60 border-b border-obsidian/10 text-xs font-bold uppercase tracking-wider text-obsidian/70">
                                <th class="py-3.5 px-5">SKU &amp; Ürün Adı</th>
                                <th class="py-3.5 px-4">Kategori</th>
                                <th class="py-3.5 px-4">Merkez Fiyatı</th>
                                <th class="py-3.5 px-4">Şube Alt / Üst Sınır</th>
                                <th class="py-3.5 px-4">Durum</th>
                                <th class="py-3.5 px-5 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-obsidian/10">
                            @foreach($products as $product)
                                <tr class="hover:bg-cream/50">
                                    <td class="py-3.5 px-5">
                                        <span class="text-[11px] font-mono font-bold text-amber-harvest">{{ $product->sku }}</span>
                                        <p class="font-display font-bold text-obsidian">{{ $product->name }}</p>
                                    </td>
                                    <td class="py-3.5 px-4 text-xs font-semibold">{{ $product->category?->name ?? '-' }}</td>
                                    <td class="py-3.5 px-4 font-display font-bold text-obsidian">
                                        {{ \App\Services\PriceCalculationService::formatCurrency((float)$product->base_price) }}
                                    </td>
                                    <td class="py-3.5 px-4 text-xs text-obsidian/70">
                                        {{ $product->min_price !== null ? \App\Services\PriceCalculationService::formatCurrency((float)$product->min_price) : '-' }}
                                        /
                                        {{ $product->max_price !== null ? \App\Services\PriceCalculationService::formatCurrency((float)$product->max_price) : '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $product->is_active ? 'bg-sage/15 text-sage' : 'bg-terracotta/15 text-terracotta' }}">
                                            {{ $product->is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-5 text-right space-x-2">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" class="px-3 py-1.5 rounded-lg bg-obsidian text-cream text-xs font-bold">Düzenle</a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" class="inline" onsubmit="return confirm('Ürünü havuzdan silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-terracotta/15 text-terracotta text-xs font-bold cursor-pointer">Sil</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
