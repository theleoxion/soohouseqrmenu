@extends('layouts.admin', [
    'title' => $branch->name . ' — Masa Yönetimi & Masa Kartı Üretici',
    'subtitle' => 'Tahmin edilemez kriptografik masa tokenları ile masa oluşturun; 3 farklı marka şablonunda SVG, PNG veya toplu PDF masa kartı indirin.',
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" x-data="{ selectedTemplate: 'obsidian' }">
        <!-- Left 4 Cols: Add Table & Batch Print -->
        <div class="lg:col-span-4 space-y-6">
            <form method="POST" action="{{ route('admin.branch.tables.store', $branch->slug) }}"
                  class="rounded-3xl bg-white border border-obsidian/10 p-6 space-y-4">
                @csrf
                <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Yeni Masa Ekle</h2>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Masa Adı / Bölümü *</label>
                    <input type="text" name="name" required placeholder="Örn: Bahçe Masa 1"
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Masa Numarası *</label>
                        <input type="text" name="table_number" required placeholder="B-01"
                               class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Sıra No</label>
                        <input type="number" name="sort_order" value="{{ $tables->count() + 1 }}"
                               class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition cursor-pointer">
                    + Masa ve Benzersiz Token Üret
                </button>
            </form>

            <!-- Template Selector & Batch PDF Generator -->
            <div class="rounded-3xl bg-obsidian text-cream p-6 space-y-4">
                <h2 class="text-base font-display font-bold text-white border-b border-white/10 pb-3">Masa Kartı Tasarım Şablonu</h2>
                <p class="text-xs text-cream/70">Seçtiğiniz şablon hem tekli SVG/PNG/PDF indirmelerinde hem de toplu PDF baskısında kullanılır.</p>

                <div class="space-y-2.5">
                    @foreach($templates as $tKey => $tpl)
                        <label class="block p-3 rounded-2xl border cursor-pointer transition"
                               :class="selectedTemplate === '{{ $tKey }}' ? 'bg-amber-harvest/20 border-amber-harvest' : 'bg-white/5 border-white/10'">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="tpl_choice" value="{{ $tKey }}" x-model="selectedTemplate">
                                <div>
                                    <p class="text-xs font-bold text-white">{{ $tpl['name'] }}</p>
                                    <p class="text-[11px] text-cream/65">{{ $tpl['description'] }}</p>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                @if($tables->isNotEmpty())
                    <div class="pt-3 border-t border-white/10">
                        <a :href="'{{ route('admin.branch.tables.batch_pdf', $branch->slug) }}?template=' + selectedTemplate"
                           class="block w-full py-3.5 px-4 rounded-xl bg-amber-harvest text-white text-center text-xs font-bold uppercase tracking-wider hover:bg-amber-light hover:text-obsidian transition">
                            Tüm Masaları Tek PDF'te İndir (Toplu Baskı)
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 8 Cols: Tables List & Card Downloads -->
        <div class="lg:col-span-8">
            @if($tables->isEmpty())
                <div class="rounded-3xl bg-white border border-obsidian/10 p-12 text-center space-y-3">
                    <h3 class="text-lg font-display font-bold text-obsidian">Bu Şubede Henüz Masa Tanımlanmadı</h3>
                    <p class="text-xs text-obsidian/65 max-w-md mx-auto">
                        Sol taraftaki formdan masa eklediğinizde her masa için tahmin edilemez benzersiz bir token üretilir ve SVG, PNG, PDF masa kartları hazır hale gelir.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($tables as $table)
                        <div class="rounded-3xl bg-white border border-obsidian/10 p-6 flex flex-col justify-between space-y-4 shadow-2xs">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-1 rounded-lg bg-obsidian text-cream text-xs font-display font-bold">
                                        #{{ $table->table_number }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $table->is_active ? 'bg-sage/15 text-sage' : 'bg-terracotta/15 text-terracotta' }}">
                                        {{ $table->is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </div>

                                <h3 class="text-lg font-display font-bold text-obsidian">{{ $table->name }}</h3>
                                <p class="text-[11px] font-mono text-obsidian/50 break-all">
                                    Token: {{ $table->token }}
                                </p>
                                <a href="{{ $table->qrMenuUrl() }}" target="_blank" class="inline-block text-xs font-bold text-amber-harvest hover:underline">
                                    Masaya Özel QR Bağlantısını Test Et ↗
                                </a>
                            </div>

                            <!-- Download Formats: SVG, PNG, PDF -->
                            <div class="pt-3 border-t border-obsidian/10 space-y-2">
                                <span class="block text-[10px] font-bold uppercase tracking-wider text-obsidian/50">Masa Kartı İndir (Seçili Şablonla)</span>
                                <div class="grid grid-cols-3 gap-2 text-xs font-bold">
                                    <a :href="'{{ route('admin.branch.tables.card', [$branch->slug, $table->id, 'svg']) }}?template=' + selectedTemplate"
                                       class="py-2 px-3 rounded-xl bg-cream border border-obsidian/15 text-center hover:bg-obsidian hover:text-cream transition">
                                        SVG İndir
                                    </a>
                                    <a :href="'{{ route('admin.branch.tables.card', [$branch->slug, $table->id, 'png']) }}?template=' + selectedTemplate"
                                       class="py-2 px-3 rounded-xl bg-cream border border-obsidian/15 text-center hover:bg-obsidian hover:text-cream transition">
                                        PNG İndir
                                    </a>
                                    <a :href="'{{ route('admin.branch.tables.card', [$branch->slug, $table->id, 'pdf']) }}?template=' + selectedTemplate"
                                       class="py-2 px-3 rounded-xl bg-amber-harvest text-white text-center hover:bg-obsidian transition">
                                        PDF İndir
                                    </a>
                                </div>
                            </div>

                            <!-- Quick Update / Active Toggle -->
                            <div class="pt-3 border-t border-obsidian/10 flex items-center justify-between gap-2">
                                <form method="POST" action="{{ route('admin.branch.tables.update', [$branch->slug, $table->id]) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $table->name }}">
                                    <input type="hidden" name="table_number" value="{{ $table->table_number }}">
                                    <input type="hidden" name="is_active" value="{{ $table->is_active ? '0' : '1' }}">
                                    <button type="submit" class="text-xs font-bold text-obsidian/70 hover:text-obsidian underline cursor-pointer">
                                        {{ $table->is_active ? 'Pasife Al' : 'Aktifleştir' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.branch.tables.destroy', [$branch->slug, $table->id]) }}" onsubmit="return confirm('Masayı silmek istediğinize emin misiniz?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-terracotta hover:underline cursor-pointer">Masayı Sil</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
