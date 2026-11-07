<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Şifremi Unuttum — Soohouse Coffee®</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-obsidian text-cream flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md space-y-6">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-display font-bold text-white">Şifre Sıfırlama</h1>
            <p class="text-xs text-cream/65">Kayıtlı e-posta adresinizi girin; sıfırlama bağlantısını gönderelim.</p>
        </div>

        <div class="rounded-3xl bg-cream text-obsidian p-8 shadow-2xl space-y-5">
            @if(session('status'))
                <div class="p-3.5 rounded-xl bg-sage/10 border border-sage/30 text-sage text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-terracotta/10 border border-terracotta/30 text-terracotta text-xs">
                    @foreach($errors->all() as $err)
                        <p>• {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">E-posta Adresi</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="w-full rounded-xl bg-white border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                </div>
                <button type="submit" class="w-full py-3.5 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                    Sıfırlama Bağlantısı Gönder
                </button>
            </form>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs font-bold text-obsidian/70 hover:text-obsidian">← Giriş Ekranına Dön</a>
            </div>
        </div>
    </div>
</body>
</html>
