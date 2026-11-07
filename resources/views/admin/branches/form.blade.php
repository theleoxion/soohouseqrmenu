@extends('layouts.admin', [
    'title' => $branch->exists ? $branch->name . ' — Şube Düzenle' : 'Yeni Şube Oluştur',
    'subtitle' => 'Şube bilgilerini, harita koordinatlarını, haftalık çalışma saatlerini ve SEO alanlarını yönetin.',
])

@section('content')
    <form method="POST"
          action="{{ $branch->exists ? route('admin.branches.update', $branch->slug) : route('admin.branches.store') }}"
          enctype="multipart/form-data"
          class="space-y-8">
        @csrf
        @if($branch->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left 7 Cols: Core Details -->
            <div class="lg:col-span-7 space-y-6">
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-5">
                    <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Temel Şube Bilgileri</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Şube Adı *</label>
                            <input type="text" name="name" value="{{ old('name', $branch->name) }}" required placeholder="Örn: Soohouse Moda"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">URL Slug (Boş bırakılırsa otomatik)</label>
                            <input type="text" name="slug" value="{{ old('slug', $branch->slug) }}" placeholder="soohouse-moda"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">İl (Şehir) *</label>
                            <input type="text" name="city" value="{{ old('city', $branch->city) }}" required placeholder="İstanbul"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">İlçe</label>
                            <input type="text" name="district" value="{{ old('district', $branch->district) }}" placeholder="Kadıköy"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Açık Adres *</label>
                        <textarea name="address" rows="2" required class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">{{ old('address', $branch->address) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Enlem (Latitude)</label>
                            <input type="text" name="latitude" value="{{ old('latitude', $branch->latitude) }}" placeholder="40.9819"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Boylam (Longitude)</label>
                            <input type="text" name="longitude" value="{{ old('longitude', $branch->longitude) }}" placeholder="29.0268"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Sıralama</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $branch->sort_order ?? 1) }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Telefon</label>
                            <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">WhatsApp Numarası</label>
                            <input type="text" name="whatsapp" value="{{ old('whatsapp', $branch->whatsapp) }}" placeholder="905XXXXXXXXX"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">E-posta</label>
                            <input type="email" name="email" value="{{ old('email', $branch->email) }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Instagram URL</label>
                            <input type="text" name="instagram_url" value="{{ old('instagram_url', $branch->instagram_url) }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Facebook URL</label>
                            <input type="text" name="facebook_url" value="{{ old('facebook_url', $branch->facebook_url) }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Google Haritalar Linki</label>
                            <input type="text" name="google_maps_url" value="{{ old('google_maps_url', $branch->google_maps_url) }}"
                                   class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Şube Tanıtım Metni</label>
                        <textarea name="description" rows="3" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">{{ old('description', $branch->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Kapak Görseli (JPG/PNG/WebP)</label>
                            <input type="file" name="cover_image_file" accept="image/*" class="text-xs">
                        </div>
                        <div class="pt-4">
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch->is_active)) class="w-4 h-4 rounded">
                                <span class="text-sm font-bold text-obsidian">Şube Aktif (Sitede ve QR Menüde Açık)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-4">
                    <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Şube SEO Alanları</h2>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">SEO Başlığı (Title)</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $branch->seo_title) }}"
                               class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">SEO Açıklaması (Meta Description)</label>
                        <textarea name="seo_description" rows="2" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2.5 text-sm">{{ old('seo_description', $branch->seo_description) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Right 5 Cols: Weekly Hours -->
            <div class="lg:col-span-5 space-y-6">
                <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-4">
                    <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Haftalık Çalışma Saatleri</h2>
                    <div class="space-y-3">
                        @for($day = 1; $day <= 7; $day++)
                            @php $h = $hours->get($day); @endphp
                            <div class="p-3 rounded-xl bg-cream border border-obsidian/10 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <span class="font-bold text-obsidian w-24">{{ \App\Models\BranchHour::dayName($day) }}</span>
                                <div class="flex items-center gap-1.5">
                                    <input type="time" name="hours[{{ $day }}][open_time]" value="{{ substr($h->open_time ?? '08:00', 0, 5) }}"
                                           class="rounded-lg border border-obsidian/20 bg-white px-2 py-1">
                                    <span>-</span>
                                    <input type="time" name="hours[{{ $day }}][close_time]" value="{{ substr($h->close_time ?? '23:00', 0, 5) }}"
                                           class="rounded-lg border border-obsidian/20 bg-white px-2 py-1">
                                </div>
                                <label class="inline-flex items-center gap-1.5 text-terracotta font-semibold cursor-pointer">
                                    <input type="checkbox" name="hours[{{ $day }}][is_closed]" value="1" @checked($h->is_closed ?? false)>
                                    <span>Kapalı</span>
                                </label>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="flex-1 py-4 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                        {{ $branch->exists ? 'Şube Bilgilerini Güncelle' : 'Şubeyi Oluştur ve Yayına Al' }}
                    </button>
                    <a href="{{ route('admin.branches.index') }}" class="py-4 px-5 rounded-xl border border-obsidian/20 text-xs font-bold text-obsidian">
                        İptal
                    </a>
                </div>
            </div>
        </div>
    </form>
@endsection
