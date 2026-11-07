@extends('layouts.public', [
    'breadcrumbs' => [['name' => 'Franchise Başvurusu', 'url' => route('public.franchise')]],
])

@section('content')
    <section class="bg-obsidian text-cream py-16 sm:py-20 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
            <span class="text-xs font-bold uppercase tracking-widest text-amber-harvest">YATIRIMCI EKOSİSTEMİ</span>
            <h1 class="text-3xl sm:text-5xl font-display font-extrabold text-white mt-2">
                {{ $siteSettings['franchise_title'] ?? 'Soohouse Coffee® Ailesine Yatırımcı Olarak Katılın' }}
            </h1>
            <p class="text-sm sm:text-base text-cream/80 mt-4 leading-relaxed max-w-2xl">
                {{ $siteSettings['franchise_subtitle'] ?? 'Lokasyon analizinden mimari projelendirmeye, ekipman tedarikinden barista akademisine ve merkezi dijital menü/fiyat yönetimine kadar tüm aşamalarda yanınızdayız.' }}
            </p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Left Column: Franchise Advantages -->
            <div class="lg:col-span-5 space-y-6">
                <div class="p-8 rounded-3xl bg-white border border-obsidian/10 space-y-6 shadow-sm">
                    <h2 class="text-2xl font-display font-bold text-obsidian">Neden Soohouse Coffee® Franchise?</h2>

                    <div class="space-y-5 text-sm">
                        <div class="flex gap-4">
                            <span class="w-8 h-8 rounded-xl bg-obsidian text-amber-light font-display font-bold flex items-center justify-center shrink-0">1</span>
                            <div>
                                <h3 class="font-bold text-obsidian">Merkezi Kavrum &amp; Ürün Havuzu</h3>
                                <p class="text-obsidian/70 text-xs mt-1 leading-relaxed">Tüm şubelerimize standart ve taze kavrulmuş kahve çekirdekleri ile test edilmiş reçeteler doğrudan merkezden ulaştırılır.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <span class="w-8 h-8 rounded-xl bg-obsidian text-amber-light font-display font-bold flex items-center justify-center shrink-0">2</span>
                            <div>
                                <h3 class="font-bold text-obsidian">Anahtar Teslim Mimari &amp; Kurulum</h3>
                                <p class="text-obsidian/70 text-xs mt-1 leading-relaxed">Marka kimliğimize uygun endüstriyel-zanaatkâr mimari proje, profesyonel espresso ekipmanı seçimi ve saha kurulumu.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <span class="w-8 h-8 rounded-xl bg-obsidian text-amber-light font-display font-bold flex items-center justify-center shrink-0">3</span>
                            <div>
                                <h3 class="font-bold text-obsidian">Barista Akademisi &amp; Operasyon Desteği</h3>
                                <p class="text-obsidian/70 text-xs mt-1 leading-relaxed">Açılış öncesi ve sonrası kapsamlı barista eğitimi, kalite denetimi ve dijital şube yönetim paneli.</p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <span class="w-8 h-8 rounded-xl bg-obsidian text-amber-light font-display font-bold flex items-center justify-center shrink-0">4</span>
                            <div>
                                <h3 class="font-bold text-obsidian">Canlı QR Menü &amp; Fiyat Esnekliği</h3>
                                <p class="text-obsidian/70 text-xs mt-1 leading-relaxed">Basılı menü maliyeti olmadan masaya özel QR kartları, anlık şube indirimi ve müşteri geri bildirim takibi.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Franchise Application Form -->
            <div class="lg:col-span-7">
                <div class="p-8 sm:p-10 rounded-3xl bg-white border border-obsidian/10 shadow-sm">
                    <h2 class="text-2xl font-display font-bold text-obsidian mb-2">Franchise Başvuru Formu</h2>
                    <p class="text-xs text-obsidian/65 mb-6">Aşağıdaki formu eksiksiz doldurun; Franchise ve Yatırımcı İlişkileri birimimiz sizinle iletişime geçsin.</p>

                    @if(session('franchise_success'))
                        <div class="mb-6 p-5 rounded-2xl bg-sage/10 border border-sage/30 text-sage text-sm font-semibold">
                            {{ session('franchise_success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 p-4 rounded-2xl bg-terracotta/10 border border-terracotta/30 text-terracotta text-xs space-y-1">
                            @foreach($errors->all() as $error)
                                <p>• {{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('public.franchise.store') }}" class="space-y-5">
                        @csrf
                        <!-- Honeypot anti-spam field -->
                        <div class="hidden" aria-hidden="true">
                            <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="full_name" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">Ad Soyad *</label>
                                <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" required
                                       class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                            </div>
                            <div>
                                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">Telefon Numarası *</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="05XX XXX XX XX"
                                       class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">E-posta Adresi *</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                       class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                            </div>
                            <div>
                                <label for="city" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">Düşünülen Şehir *</label>
                                <input type="text" id="city" name="city" value="{{ old('city') }}" required placeholder="Örn: İstanbul"
                                       class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                            </div>
                            <div>
                                <label for="district" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">İlçe / Bölge</label>
                                <input type="text" id="district" name="district" value="{{ old('district') }}" placeholder="Örn: Kadıköy"
                                       class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="investment_budget" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">Planlanan Yatırım Bütçesi *</label>
                                <select id="investment_budget" name="investment_budget" required
                                        class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm bg-white focus:outline-none focus:border-amber-harvest">
                                    <option value="">Seçiniz...</option>
                                    <option value="2.000.000 ₺ - 3.500.000 ₺" @selected(old('investment_budget') === '2.000.000 ₺ - 3.500.000 ₺')>2.000.000 ₺ - 3.500.000 ₺</option>
                                    <option value="3.500.000 ₺ - 5.000.000 ₺" @selected(old('investment_budget') === '3.500.000 ₺ - 5.000.000 ₺')>3.500.000 ₺ - 5.000.000 ₺</option>
                                    <option value="5.000.000 ₺ - 7.500.000 ₺" @selected(old('investment_budget') === '5.000.000 ₺ - 7.500.000 ₺')>5.000.000 ₺ - 7.500.000 ₺</option>
                                    <option value="7.500.000 ₺ ve üzeri" @selected(old('investment_budget') === '7.500.000 ₺ ve üzeri')>7.500.000 ₺ ve üzeri</option>
                                </select>
                            </div>
                            <div>
                                <label for="preferred_location_note" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">Mevcut Mağaza / Aday Lokasyon Durumu</label>
                                <input type="text" id="preferred_location_note" name="preferred_location_note" value="{{ old('preferred_location_note') }}" placeholder="Mülküm var / Kiralık yer bakıyorum"
                                       class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">
                            </div>
                        </div>

                        <div>
                            <label for="message" class="block text-xs font-bold uppercase tracking-wider text-obsidian/80 mb-1.5">Notunuz / Mesajınız</label>
                            <textarea id="message" name="message" rows="4" placeholder="Ticari tecrübeniz veya aday lokasyon hakkında eklemek istedikleriniz..."
                                      class="w-full rounded-xl border border-obsidian/20 px-4 py-3 text-sm focus:outline-none focus:border-amber-harvest">{{ old('message') }}</textarea>
                        </div>

                        <!-- KVKK Consent Box -->
                        <div class="p-4 rounded-2xl bg-sand/60 border border-obsidian/10 space-y-2">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" name="kvkk_accepted" value="1" required @checked(old('kvkk_accepted'))
                                       class="mt-1 w-4 h-4 rounded border-obsidian/30 text-amber-harvest focus:ring-amber-harvest">
                                <span class="text-xs text-obsidian/80 leading-relaxed">
                                    <strong>KVKK Aydınlatma ve Açık Rıza Onayı *:</strong>
                                    {{ $siteSettings['kvkk_text'] ?? '6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında bilgilerimin franchise değerlendirme süreçleri için işlenmesini kabul ediyorum.' }}
                                </span>
                            </label>
                        </div>

                        <button type="submit"
                                class="w-full py-4 px-6 rounded-full bg-obsidian text-cream text-xs font-bold uppercase tracking-widest hover:bg-amber-harvest transition shadow-md cursor-pointer">
                            Franchise Başvurusunu Gönder
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
