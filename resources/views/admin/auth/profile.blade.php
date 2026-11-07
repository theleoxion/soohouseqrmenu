@extends('layouts.admin', [
    'title' => 'Profil ve Şifre Yönetimi',
    'subtitle' => 'Hesap bilgilerinizi ve giriş şifrenizi güncelleyin.',
])

@section('content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.profile.update') }}" class="rounded-3xl bg-white border border-obsidian/10 p-8 space-y-6 shadow-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Ad Soyad *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full rounded-xl border border-obsidian/20 px-4 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full rounded-xl border border-obsidian/20 px-4 py-2.5 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/50 mb-1.5">E-posta Adresi (Salt Okunur)</label>
                <input type="email" value="{{ $user->email }}" disabled
                       class="w-full rounded-xl bg-sand/60 border border-obsidian/10 px-4 py-2.5 text-sm text-obsidian/60">
            </div>

            <div class="pt-4 border-t border-obsidian/10 space-y-4">
                <h2 class="text-base font-display font-bold text-obsidian">Şifre Değiştir (Opsiyonel)</h2>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Mevcut Şifre</label>
                    <input type="password" name="current_password"
                           class="w-full rounded-xl border border-obsidian/20 px-4 py-2.5 text-sm">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Yeni Şifre (En az 8 karakter)</label>
                        <input type="password" name="new_password"
                               class="w-full rounded-xl border border-obsidian/20 px-4 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Yeni Şifre (Tekrar)</label>
                        <input type="password" name="new_password_confirmation"
                               class="w-full rounded-xl border border-obsidian/20 px-4 py-2.5 text-sm">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="px-6 py-3 rounded-xl bg-obsidian text-cream text-xs font-bold uppercase tracking-wider hover:bg-amber-harvest transition cursor-pointer">
                    Değişiklikleri Kaydet
                </button>
            </div>
        </form>
    </div>
@endsection
