<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Yeni Şifre Belirle — Soohouse Coffee®</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-obsidian text-cream flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md space-y-6">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-display font-bold text-white">Yeni Şifre Belirle</h1>
        </div>

        <div class="rounded-3xl bg-cream text-obsidian p-8 shadow-2xl space-y-5">
            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-terracotta/10 border border-terracotta/30 text-terracotta text-xs">
                    @foreach($errors->all() as $err)
                        <p>• {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">E-posta Adresi</label>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required
                           class="w-full rounded-xl bg-white border border-obsidian/20 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Yeni Şifre</label>
                    <input type="password" name="password" required
                           class="w-full rounded-xl bg-white border border-obsidian/20 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">Yeni Şifre (Tekrar)</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-xl bg-white border border-obsidian/20 px-4 py-3 text-sm">
                </div>
                <button type="submit" class="w-full py-3.5 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                    Şifreyi Güncelle
                </button>
            </form>
        </div>
    </div>
</body>
</html>
