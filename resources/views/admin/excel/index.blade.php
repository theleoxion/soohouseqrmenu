@extends('layouts.admin', [
    'title' => 'Excel İçe / Dışa Aktarma Merkezi',
    'subtitle' => 'Şablon indirme, yükleme öncesi doğrulama ve önizleme, satır bazlı hata raporu ve upsert (ekleme/güncelleme) desteği.',
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left 7 Cols: Product Import with Validation & Preview -->
        <div class="lg:col-span-7 space-y-6">
            @if(auth()->user()->hasPermission('excel.import'))
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-obsidian/10 pb-4">
                        <div>
                            <h2 class="text-base font-display font-bold text-obsidian">Excel'den Toplu Ürün Yükleme (Upsert)</h2>
                            <p class="text-xs text-obsidian/60">SKU kodu mevcutsa ürünü günceller, yoksa yeni ürün olarak ekler.</p>
                        </div>
                        <a href="{{ route('admin.excel.template') }}"
                           class="px-4 py-2 rounded-xl bg-sand text-obsidian text-xs font-bold hover:bg-obsidian hover:text-cream transition">
                            Örnek Excel Şablonunu İndir (.xlsx)
                        </a>
                    </div>

                    <form method="POST" action="{{ route('admin.excel.preview') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Excel Dosyası Seçin (.xlsx, .xls, .csv) *</label>
                            <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" required
                                   class="w-full rounded-xl border border-obsidian/20 p-3 text-xs bg-cream">
                        </div>
                        <button type="submit" class="px-6 py-3 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition cursor-pointer">
                            Dosyayı Doğrula ve Önizle
                        </button>
                    </form>
                </div>

                @if($previewResult)
                    <div class="rounded-3xl bg-white border-2 border-amber-harvest p-6 sm:p-8 space-y-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-obsidian/10 pb-3">
                            <div>
                                <h3 class="text-lg font-display font-bold text-obsidian">Doğrulama ve Önizleme Raporu</h3>
                                <p class="text-xs text-obsidian/65">
                                    Toplam {{ $previewResult['total_rows'] }} satır incelendi:
                                    <strong class="text-sage">{{ count($previewResult['valid_rows']) }} geçerli satır</strong>,
                                    <strong class="text-terracotta">{{ count($previewResult['errors']) }} hatalı satır</strong>.
                                </p>
                            </div>

                            @if(!empty($previewResult['valid_rows']))
                                <form method="POST" action="{{ route('admin.excel.commit') }}">
                                    @csrf
                                    <button type="submit" class="px-6 py-3 rounded-xl bg-sage text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                                        ✓ {{ count($previewResult['valid_rows']) }} Geçerli Ürünü İçe Aktar
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if(!empty($previewResult['errors']))
                            <div class="p-4 rounded-2xl bg-terracotta/10 border border-terracotta/30 space-y-1.5 text-xs text-terracotta">
                                <p class="font-bold">Satır Bazlı Hata Raporu:</p>
                                @foreach($previewResult['errors'] as $err)
                                    <p>• <strong>Satır {{ $err['row'] }} (SKU: {{ $err['sku'] }}):</strong> {{ $err['message'] }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($previewResult['valid_rows']))
                            <div class="overflow-x-auto max-h-80">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="bg-sand/60 border-b border-obsidian/10 font-bold">
                                            <th class="py-2 px-3">Satır</th>
                                            <th class="py-2 px-3">İşlem</th>
                                            <th class="py-2 px-3">SKU</th>
                                            <th class="py-2 px-3">Ürün Adı</th>
                                            <th class="py-2 px-3">Kategori</th>
                                            <th class="py-2 px-3">Merkez Fiyatı</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-obsidian/10">
                                        @foreach($previewResult['valid_rows'] as $vRow)
                                            <tr>
                                                <td class="py-2 px-3">{{ $vRow['row'] }}</td>
                                                <td class="py-2 px-3 font-bold {{ $vRow['action'] === 'create' ? 'text-sage' : 'text-amber-harvest' }}">
                                                    {{ $vRow['action'] === 'create' ? 'Yeni Ekle' : 'Güncelle' }}
                                                </td>
                                                <td class="py-2 px-3 font-mono">{{ $vRow['sku'] }}</td>
                                                <td class="py-2 px-3 font-bold">{{ $vRow['name'] }}</td>
                                                <td class="py-2 px-3">{{ $vRow['category_name'] }}</td>
                                                <td class="py-2 px-3 font-bold">{{ \App\Services\PriceCalculationService::formatCurrency((float)$vRow['base_price']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <!-- Right 5 Cols: Excel Exports -->
        <div class="lg:col-span-5 space-y-6">
            @if(auth()->user()->hasPermission('excel.export'))
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-5">
                    <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Excel Dışa Aktarma Raporları (.xlsx)</h2>

                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-cream border border-obsidian/10 flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-obsidian">Merkez Ürün Havuzu</p>
                                <p class="text-[11px] text-obsidian/60">Tüm ürünler, SKU, kategoriler ve merkez fiyatları</p>
                            </div>
                            <a href="{{ route('admin.excel.export.products') }}" class="px-4 py-2 rounded-xl bg-obsidian text-cream text-xs font-bold shrink-0">
                                İndir (.xlsx)
                            </a>
                        </div>

                        <form method="GET" action="{{ route('admin.excel.export.branch_prices') }}" class="p-4 rounded-2xl bg-cream border border-obsidian/10 space-y-3">
                            <div>
                                <p class="text-xs font-bold text-obsidian">Şube Bazlı Fiyat &amp; İndirim Listesi</p>
                                <p class="text-[11px] text-obsidian/60">Merkez fiyatı, şube özel fiyatı, indirim ve son müşteri fiyatı</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select name="branch_id" class="flex-1 rounded-xl border border-obsidian/20 bg-white px-3 py-2 text-xs">
                                    <option value="">Tüm Şubeler</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-4 py-2 rounded-xl bg-obsidian text-cream text-xs font-bold cursor-pointer">
                                    İndir (.xlsx)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
