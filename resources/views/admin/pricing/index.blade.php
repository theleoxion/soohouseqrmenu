@extends('layouts.admin', [
    'title' => 'Merkez Toplu Fiyat Güncelleme',
    'subtitle' => 'Tüm şubelere veya seçili şubelere toplu fiyat gönderin; şube özel fiyatlarını ezme/koruma seçeneği ve işlem öncesi önizleme ile onaylayın.',
])

@section('content')
    <div class="space-y-8">
        <!-- Configuration Form -->
        <form method="POST" action="{{ route('admin.pricing.preview') }}" class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-6">
            @csrf
            <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">1. Toplu Fiyat Güncelleme Kriterleri</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Hedef Kapsam *</label>
                    <select name="target_scope" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm bg-white">
                        <option value="central_and_branches" @selected(($filters['target_scope'] ?? '') === 'central_and_branches')>Merkez Fiyatı ve Tüm Şubeler</option>
                        <option value="selected_branches" @selected(($filters['target_scope'] ?? '') === 'selected_branches')>Sadece Seçili Şubeler</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">İşlem Türü *</label>
                    <select name="operation" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm bg-white">
                        <option value="percentage_increase" @selected(($filters['operation'] ?? '') === 'percentage_increase')>Yüzde Zam (+%)</option>
                        <option value="percentage_decrease" @selected(($filters['operation'] ?? '') === 'percentage_decrease')>Yüzde İndirim (-%)</option>
                        <option value="fixed_add" @selected(($filters['operation'] ?? '') === 'fixed_add')>Sabit Tutar Ekle (+₺)</option>
                        <option value="fixed_subtract" @selected(($filters['operation'] ?? '') === 'fixed_subtract')>Sabit Tutar Düş (-₺)</option>
                        <option value="exact_price" @selected(($filters['operation'] ?? '') === 'exact_price')>Sabit Yeni Fiyat Belirle (=₺)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Oran veya Tutar Değeri *</label>
                    <input type="number" step="0.01" name="amount" value="{{ $filters['amount'] ?? 10 }}" required
                           class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Kategori Filtresi (Boş bırakılırsa tüm kategoriler)</label>
                    <select name="category_ids[]" multiple class="w-full rounded-xl border border-obsidian/20 p-3 text-xs h-32 bg-white">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(in_array($cat->id, $filters['category_ids'] ?? []))>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Şube Filtresi (Seçili şubeler modu için)</label>
                    <select name="branch_ids[]" multiple class="w-full rounded-xl border border-obsidian/20 p-3 text-xs h-32 bg-white">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(in_array($b->id, $filters['branch_ids'] ?? []))>{{ $b->name }} ({{ $b->city }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-cream border border-obsidian/10 flex flex-wrap items-center justify-between gap-4">
                <label class="inline-flex items-center gap-2.5 text-xs font-bold text-obsidian cursor-pointer">
                    <input type="checkbox" name="overwrite_branch_custom" value="1" @checked($filters['overwrite_branch_custom'] ?? false) class="w-4 h-4 rounded">
                    <span>Şubelerin kendi tanımladığı özel fiyatları (override) EZ (İşaretlenmezse özel şube fiyatları korunur)</span>
                </label>

                <button type="submit" class="px-6 py-3 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition cursor-pointer">
                    Değişiklikleri Önizle (Eski → Yeni Fiyat)
                </button>
            </div>
        </form>

        <!-- Preview & Confirmation Step -->
        @if($preview !== null)
            <div class="rounded-3xl bg-white border-2 border-amber-harvest p-6 sm:p-8 space-y-6 shadow-lg">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-obsidian/10 pb-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">ONAY ÖNCESİ ÖNİZLEME</span>
                        <h2 class="text-xl font-display font-bold text-obsidian">2. Eski → Yeni Fiyat Karşılaştırması ({{ $preview->count() }} Ürün)</h2>
                    </div>

                    <form method="POST" action="{{ route('admin.pricing.apply') }}">
                        @csrf
                        <input type="hidden" name="target_scope" value="{{ $filters['target_scope'] }}">
                        <input type="hidden" name="operation" value="{{ $filters['operation'] }}">
                        <input type="hidden" name="amount" value="{{ $filters['amount'] }}">
                        @if(!empty($filters['overwrite_branch_custom']))
                            <input type="hidden" name="overwrite_branch_custom" value="1">
                        @endif
                        @foreach(($filters['branch_ids'] ?? []) as $bid)
                            <input type="hidden" name="branch_ids[]" value="{{ $bid }}">
                        @endforeach
                        @foreach(($filters['category_ids'] ?? []) as $cid)
                            <input type="hidden" name="category_ids[]" value="{{ $cid }}">
                        @endforeach
                        <button type="submit" class="px-7 py-3.5 rounded-xl bg-sage text-white text-xs font-bold uppercase tracking-widest hover:opacity-95 transition shadow-md cursor-pointer">
                            ✓ Onayla ve Toplu Fiyat Güncellemesini Uygula
                        </button>
                    </form>
                </div>

                @if($preview->isEmpty())
                    <p class="text-xs text-obsidian/60 py-6 text-center">Seçilen kriterlere uygun ürün bulunamadı.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-sand/60 border-b border-obsidian/10 font-bold uppercase tracking-wider text-obsidian/70">
                                    <th class="py-3 px-4">SKU &amp; Ürün</th>
                                    <th class="py-3 px-4">Kategori</th>
                                    <th class="py-3 px-4">Eski Merkez Fiyatı</th>
                                    <th class="py-3 px-4">Yeni Merkez Fiyatı</th>
                                    <th class="py-3 px-4">Şube Etkisi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-obsidian/10">
                                @foreach($preview as $row)
                                    <tr>
                                        <td class="py-3 px-4 font-bold text-obsidian">{{ $row['sku'] }} — {{ $row['product_name'] }}</td>
                                        <td class="py-3 px-4">{{ $row['category_name'] }}</td>
                                        <td class="py-3 px-4 text-obsidian/60">{{ \App\Services\PriceCalculationService::formatCurrency((float)$row['old_base_price']) }}</td>
                                        <td class="py-3 px-4 font-bold text-sage">{{ \App\Services\PriceCalculationService::formatCurrency((float)$row['new_base_price']) }}</td>
                                        <td class="py-3 px-4 space-y-1">
                                            @foreach($row['branches'] as $bDiff)
                                                <div>
                                                    <strong>{{ $bDiff['branch_name'] }}:</strong>
                                                    {{ \App\Services\PriceCalculationService::formatCurrency((float)$bDiff['old_price']) }}
                                                    →
                                                    <span class="font-bold text-amber-harvest">{{ \App\Services\PriceCalculationService::formatCurrency((float)$bDiff['new_price']) }}</span>
                                                    <span class="text-obsidian/50">({{ $bDiff['action_note'] }})</span>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
