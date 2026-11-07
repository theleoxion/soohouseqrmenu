@extends('layouts.admin', [
    'title' => 'Merkez Kategori Yönetimi',
    'subtitle' => 'Ürün kategorilerini oluşturun, sürükle-bırak ile sıralayın ve aktif/pasif durumlarını yönetin.',
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8"
         x-data="{
             draggingId: null,
             items: {{ Js::from($categories->pluck('id')) }},
             saveOrder() {
                 fetch('{{ route('admin.categories.reorder') }}', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'Accept': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}'
                     },
                     body: JSON.stringify({ ordered_ids: this.items })
                 }).then(() => window.location.reload());
             }
         }">

        <!-- Left 4 Cols: Add Category -->
        <div class="lg:col-span-4">
            <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data"
                  class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                @csrf
                <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Yeni Kategori Ekle</h2>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Kategori Adı *</label>
                    <input type="text" name="name" required placeholder="Örn: Espresso Bazlı Kahveler"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Açıklama</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Sıra No</label>
                        <input type="number" name="sort_order" value="{{ $categories->count() + 1 }}"
                               class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                    <div class="pt-6">
                        <label class="inline-flex items-center gap-2 text-xs font-bold cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>Aktif</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Kategori Görseli</label>
                    <input type="file" name="image_file" accept="image/*" class="text-xs">
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition cursor-pointer">
                    + Kategori Oluştur
                </button>
            </form>
        </div>

        <!-- Right 8 Cols: Category List & Drag-and-Drop Reorder -->
        <div class="lg:col-span-8 space-y-4">
            @if($categories->isEmpty())
                <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center space-y-2">
                    <h3 class="text-lg font-display font-bold text-obsidian">Henüz Kategori Tanımlanmadı</h3>
                    <p class="text-xs text-obsidian/65">Merkez ürün havuzuna ürün ekleyebilmek için sol taraftaki formdan ilk kategorinizi oluşturun.</p>
                </div>
            @else
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-obsidian/10 pb-3">
                        <div>
                            <h2 class="text-base font-display font-bold text-obsidian">Kategoriler ({{ $categories->count() }})</h2>
                            <p class="text-xs text-obsidian/60">Yukarı/Aşağı oklarıyla veya sıra numarasıyla sıralamayı anında değiştirebilirsiniz.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($categories as $index => $cat)
                            <div class="p-4 rounded-2xl bg-cream border border-obsidian/10 space-y-3" x-data="{ editOpen: false }">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-8 h-8 rounded-lg bg-obsidian text-cream font-display font-bold text-xs flex items-center justify-center">
                                            {{ $cat->sort_order }}
                                        </span>
                                        <div>
                                            <h3 class="font-display font-bold text-sm text-obsidian">{{ $cat->name }}</h3>
                                            <p class="text-xs text-obsidian/55">{{ $cat->products_count }} ürün • {{ $cat->is_active ? 'Aktif' : 'Pasif' }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if($index > 0)
                                            <button type="button"
                                                    @click="const tmp = items[{{ $index }}]; items[{{ $index }}] = items[{{ $index - 1 }}]; items[{{ $index - 1 }}] = tmp; saveOrder();"
                                                    class="px-2.5 py-1.5 rounded-lg bg-white border border-obsidian/15 text-xs font-bold hover:bg-sand cursor-pointer"
                                                    title="Yukarı Taşı">↑</button>
                                        @endif
                                        @if($index < $categories->count() - 1)
                                            <button type="button"
                                                    @click="const tmp = items[{{ $index }}]; items[{{ $index }}] = items[{{ $index + 1 }}]; items[{{ $index + 1 }}] = tmp; saveOrder();"
                                                    class="px-2.5 py-1.5 rounded-lg bg-white border border-obsidian/15 text-xs font-bold hover:bg-sand cursor-pointer"
                                                    title="Aşağı Taşı">↓</button>
                                        @endif
                                        <button type="button" @click="editOpen = !editOpen" class="px-3 py-1.5 rounded-lg bg-obsidian text-cream text-xs font-bold cursor-pointer">
                                            Düzenle
                                        </button>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $cat->id) }}" onsubmit="return confirm('Kategoriyi silmek istediğinize emin misiniz?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-terracotta/15 text-terracotta text-xs font-bold cursor-pointer">Sil</button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Inline Edit Form -->
                                <form x-show="editOpen" x-cloak method="POST" action="{{ route('admin.categories.update', $cat->id) }}" enctype="multipart/form-data"
                                      class="pt-3 border-t border-obsidian/10 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="block text-[11px] font-bold text-obsidian/70 mb-1">Ad</label>
                                        <input type="text" name="name" value="{{ $cat->name }}" required class="w-full rounded-lg border border-obsidian/20 bg-white px-3 py-1.5 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-obsidian/70 mb-1">Açıklama</label>
                                        <input type="text" name="description" value="{{ $cat->description }}" class="w-full rounded-lg border border-obsidian/20 bg-white px-3 py-1.5 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-obsidian/70 mb-1">Sıra / Durum</label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="sort_order" value="{{ $cat->sort_order }}" class="w-16 rounded-lg border border-obsidian/20 bg-white px-2 py-1.5 text-xs">
                                            <label class="inline-flex items-center gap-1 text-xs font-bold">
                                                <input type="checkbox" name="is_active" value="1" @checked($cat->is_active)> Aktif
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit" class="w-full py-2 rounded-lg bg-amber-harvest text-white text-xs font-bold cursor-pointer">Güncelle</button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
