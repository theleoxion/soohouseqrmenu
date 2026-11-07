@extends('layouts.admin', [
    'title' => $branch->name . ' — Şube Bilgileri ve Çalışma Saatleri',
    'subtitle' => 'Şubenizin iletişim bilgilerini, haftalık çalışma saatlerini ve özel gün/tatil durumlarını yönetin.',
])

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left 7 Cols: Branch Contact & Weekly Hours -->
        <div class="lg:col-span-7">
            <form method="POST" action="{{ route('admin.branch.settings.update', $branch->slug) }}" class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-6">
                @csrf
                @method('PUT')

                <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">İletişim ve Konum Bilgileri</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Telefon</label>
                        <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $branch->whatsapp) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">E-posta</label>
                        <input type="email" name="email" value="{{ old('email', $branch->email) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Açık Adres *</label>
                    <textarea name="address" rows="2" required class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">{{ old('address', $branch->address) }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Enlem (Latitude)</label>
                        <input type="text" name="latitude" value="{{ old('latitude', $branch->latitude) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Boylam (Longitude)</label>
                        <input type="text" name="longitude" value="{{ old('longitude', $branch->longitude) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Instagram URL</label>
                        <input type="text" name="instagram_url" value="{{ old('instagram_url', $branch->instagram_url) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Facebook URL</label>
                        <input type="text" name="facebook_url" value="{{ old('facebook_url', $branch->facebook_url) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1">Google Harita Linki</label>
                        <input type="text" name="google_maps_url" value="{{ old('google_maps_url', $branch->google_maps_url) }}" class="w-full rounded-xl border border-obsidian/20 px-3.5 py-2 text-sm">
                    </div>
                </div>

                <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3 pt-2">Haftalık Çalışma Saatleri</h2>
                <div class="space-y-2.5">
                    @for($day = 1; $day <= 7; $day++)
                        @php $h = $hours->get($day); @endphp
                        <div class="p-3 rounded-xl bg-cream border border-obsidian/10 flex flex-wrap items-center justify-between gap-2 text-xs">
                            <span class="font-bold text-obsidian w-24">{{ \App\Models\BranchHour::dayName($day) }}</span>
                            <div class="flex items-center gap-1.5">
                                <input type="time" name="hours[{{ $day }}][open_time]" value="{{ substr($h->open_time ?? '08:00', 0, 5) }}" class="rounded-lg border border-obsidian/20 bg-white px-2 py-1">
                                <span>-</span>
                                <input type="time" name="hours[{{ $day }}][close_time]" value="{{ substr($h->close_time ?? '23:00', 0, 5) }}" class="rounded-lg border border-obsidian/20 bg-white px-2 py-1">
                            </div>
                            <label class="inline-flex items-center gap-1.5 text-terracotta font-semibold cursor-pointer">
                                <input type="checkbox" name="hours[{{ $day }}][is_closed]" value="1" @checked($h->is_closed ?? false)>
                                <span>Kapalı</span>
                            </label>
                        </div>
                    @endfor
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                    Şube Bilgilerini ve Saatleri Kaydet
                </button>
            </form>
        </div>

        <!-- Right 5 Cols: Special Days / Holiday Closures -->
        <div class="lg:col-span-5 space-y-6">
            <div class="rounded-3xl bg-white border border-obsidian/10 p-6 sm:p-8 space-y-5">
                <h2 class="text-base font-display font-bold text-obsidian border-b border-obsidian/10 pb-3">Özel Gün / Tatil Kapatma</h2>
                <p class="text-xs text-obsidian/65">Resmi tatil veya özel bakım günlerinde şubeyi tamamen kapatabilir ya da o güne özel saat tanımlayabilirsiniz.</p>

                <form method="POST" action="{{ route('admin.branch.special_days.store', $branch->slug) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Tarih *</label>
                            <input type="date" name="date" required class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Açıklama / Not</label>
                            <input type="text" name="note" placeholder="Örn: Bayram 1. Gün" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Özel Açılış (Opsiyonel)</label>
                            <input type="time" name="open_time" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-obsidian/75 mb-1">Özel Kapanış</label>
                            <input type="time" name="close_time" class="w-full rounded-xl border border-obsidian/20 px-3 py-2 text-xs">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-terracotta cursor-pointer">
                        <input type="checkbox" name="is_closed" value="1" checked>
                        <span>Tam Gün Kapalı Olarak İşaretle</span>
                    </label>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-harvest text-white text-xs font-bold uppercase tracking-wider cursor-pointer">
                        + Özel Gün Ekle
                    </button>
                </form>

                @if($branch->specialDays->isNotEmpty())
                    <div class="pt-4 border-t border-obsidian/10 space-y-2">
                        @foreach($branch->specialDays as $sd)
                            <div class="p-3 rounded-xl bg-cream flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold">{{ $sd->date->format('d.m.Y') }}</span>
                                    <span class="text-obsidian/65 ml-1">
                                        ({{ $sd->is_closed ? 'Tam Gün Kapalı' : substr((string)$sd->open_time,0,5) . '-' . substr((string)$sd->close_time,0,5) }})
                                        {{ $sd->note ? '— ' . $sd->note : '' }}
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('admin.branch.special_days.destroy', [$branch->slug, $sd->id]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-terracotta font-bold hover:underline cursor-pointer">Sil</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
