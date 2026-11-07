<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Yönetim Girişi — Soohouse Coffee®</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-obsidian text-cream flex items-center justify-center p-4 antialiased">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo Box -->
        <div class="text-center space-y-3">
            <a href="{{ route('public.home') }}" class="inline-block bg-cream px-6 py-4 rounded-2xl shadow-lg">
                <img src="{{ asset('brand/soohouse-logo.svg') }}" alt="Soohouse Coffee®" width="200" height="100" class="h-14 w-auto object-contain">
            </a>
            <h1 class="text-xl font-display font-bold text-white">Kurumsal Yönetim &amp; Şube Girişi</h1>
            <p class="text-xs text-cream/60">Rolünüze göre merkez veya şube yönetim ekranına yönlendirilirsiniz.</p>
        </div>

        <div class="rounded-3xl bg-cream text-obsidian p-8 shadow-2xl border border-white/10 space-y-5">
            @if(session('status'))
                <div class="p-3.5 rounded-xl bg-sage/10 border border-sage/30 text-sage text-xs font-semibold">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 rounded-xl bg-terracotta/10 border border-terracotta/30 text-terracotta text-xs font-medium space-y-1">
                    @foreach($errors->all() as $err)
                        <p>• {{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-obsidian/75 mb-1.5">E-posta Adresi</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl bg-white border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-obsidian/75">Şifre</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-amber-harvest hover:underline">Şifremi Unuttum</a>
                    </div>
                    <input type="password" id="password" name="password" required
                           class="w-full rounded-xl bg-white border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                </div>

                <label class="flex items-center gap-2.5 text-xs text-obsidian/75 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" class="rounded border-obsidian/30 text-amber-harvest">
                    <span>Oturumumu açık tut</span>
                </label>

                <button type="submit"
                        class="w-full py-3.5 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition cursor-pointer">
                    Giriş Yap
                </button>
            </form>
        </div>

        <div class="text-center">
            <a href="{{ route('public.home') }}" class="text-xs text-cream/60 hover:text-white transition">← Kurumsal Web Sitesine Dön</a>
        </div>
    </div>
</body>
</html>
