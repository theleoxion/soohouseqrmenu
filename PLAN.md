# SOOHOUSE COFFEE® — Kurumsal Web Platformu, Merkezi Yönetim, Şube Paneli ve QR Menü Sistemi
## Mimari ve Geliştirme Planı (`PLAN.md`)

---

## 1. Marka Kimliği ve Görsel Analiz (`logo/` İncelemesi)

`logo/` dizinindeki dosyalar (`soohouse-logo.svg`, `soohouse-icon.svg`, PNG varyasyonları, `favicon.ico`, `site.webmanifest` ve `KULLANIM.txt`) incelenmiştir:

- **Marka Adı:** **SOOHOUSE COFFEE®** (Kısa ad: *Soohouse*)
- **Sembol / İkon:** Minimalist, kalın siyah konturlu, geometrik ve karakteristik **traktör** figürü. Toprağa, üretime, kahve çiftliklerine (hasat/emek/doğallık) ve endüstriyel-zanaatkâr (artisanal & industrial) ruha gönderme yapan özgün bir amblem.
- **Amblem ve Wordmark Yapısı:** Üstte siyah çizgisel traktör ikonu, altta siyah dikdörtgen plaka (levha) içinde yuvarlatılmış uçlu geometrik sans-serif büyük harflerle beyaz **`SOOHOUSECOFFEE®`** yazısı.
- **Tasarım Dili ve Ruh Hali (Mood):**
  - **Endüstriyel Zanaat & Toprağın Gücü (From Farm to Cup):** Traktör sembolü kahvenin topraktan fincana uzanan yolculuğunu, emeği ve güçlü karakteri temsil eder.
  - **Renk Paleti (CSS Değişkenleri ile Tutarlı Tasarım Sistemi):**
    - `--color-obsidian` (`#0D0D0C`): Ana marka siyahı (logo levhasındaki derin siyah / espresso koyusu).
    - `--color-charcoal` (`#1C1B19`): İkincil koyu yüzeyler, kartlar ve karanlık bölümler.
    - `--color-roast` (`#3B2618`): Kavrulmuş kahve çekirdeği tonu.
    - `--color-amber-harvest` (`#C87D3B`): Hasat kehribarı / bakır-karamel vurgu rengi (CTA butonları, aktif sekmeler, mikro etkileşimler).
    - `--color-amber-light` (`#E5A96D`): Açık karamel / altın krema vurgusu.
    - `--color-cream` (`#F8F5F0`): Sıcak ham kağıt / süt köpüğü ana açık arka plan rengi (soğuk beyaz `#FFFFFF` yerine sıcak, davetkâr zemin).
    - `--color-sand` (`#EDE7DD`): İkincil açık yüzey, kart sınırları ve tablo satır ayrımları.
    - `--color-sage` (`#2E5A44`): Doğallık / "Şu an Açık" / başarı durumu yeşili.
    - `--color-terracotta` (`#B83B2A`): "Kapalı" / "Tükendi" / İndirim / Hata durumu kırmızısı.
- **Tipografi Seçimi ve Gerekçesi (Self-Hosted `.woff2`, `font-display: swap`):**
  - **Başlık Fontu (`--font-display`):** **Space Grotesk** (veya **Syne** / **Plus Jakarta Sans** Bold/ExtraBold) — Logodaki `SOOHOUSECOFFEE` harflerinin güçlü, geometrik, modern-endüstriyel yapısıyla kusursuz uyum sağlar. Türkçe karakterleri (`ğ, ş, ı, İ, ö, ü, ç`) eksiksiz destekler.
  - **Eşlikçi Serif Vurgusu (`--font-serif`):** **Fraunces** veya **Playfair Display** (İtalik/Vurgu başlıklarında "Topraktan Fincana" zanaatkâr dokunuşu için).
  - **Gövde ve Arayüz Fontu (`--font-sans`):** **Plus Jakarta Sans** — QR menüde, fiyatlarda, tablolarda ve uzun kurumsal metinlerde en yüksek okunabilirliği sunan, rakamları (tabular-nums) hizalı modern sans-serif. Tüm fontlar `public/fonts/` dizininde `.woff2` olarak barındırılacak, dış CDN kullanılmayacaktır.
- **Logo Optimizasyonu:**
  - `logo/` klasöründeki orijinal dosyalara kesinlikle dokunulmayacaktır.
  - `public/brand/` dizinine optimize edilmiş SVG, WebP ve PNG kopyaları ile `favicon.ico` ve `site.webmanifest` yerleştirilecektir.

---

## 2. Teknoloji Yığını ve Mimari Gerekçesi

### Seçilen Yığın
- **Backend:** **PHP 8.2+ / Laravel 11.x** (MVC + Service Layer + Policy/Gate RBAC)
- **Veritabanı:** **MySQL / MariaDB** (Geliştirme ve test ortamında hem MySQL hem SQLite tam uyumlu; cPanel'de standart MySQL/MariaDB ile çalışır)
- **Frontend:** **Blade Şablon Motoru + Alpine.js v3 + Tailwind CSS v3** (Vite ile yerelde derlenip `public/build/` içine dahil edilmiş statik CSS/JS çıktıları; üretim sunucusunda Node.js gerektirmez)
- **Excel İşlemleri:** `phpoffice/phpspreadsheet` — Sunucu tarafında `.xlsx` okuma (doğrulama, önizleme, upsert) ve `.xlsx` dışa aktarma (Türkçe karakter, UTF-8 ve `₺` para birimi formatlı).
- **QR Kod ve Masa Kartı Üretimi (SVG / PNG / PDF):**
  - **QR Kod:** `chillerlan/php-qrcode` (veya `bacon/bacon-qr-code` GD/SVG backend) — **Imagick gerektirmez**, saf PHP + GD uzantısı ile yüksek çözünürlüklü vektörel SVG ve PNG QR kod üretir.
  - **PDF Üretimi:** `barryvdh/laravel-dompdf` (`dompdf/dompdf`) — Paylaşımlı hostinglerde (cPanel) standart olan PHP `gd`, `dom`, `mbstring` uzantılarıyla çalışır; tekli veya toplu (tüm masalar tek PDF'te, baskıya hazır kesim çizgili) masa kartı çıktısı verir.
  - **SVG ve PNG Masa Kartı:** Sunucu tarafında saf SVG şablon üretimi + GD tabanlı yüksek çözünürlüklü (300 DPI eşdeğeri) PNG masa kartı oluşturucu ve istemci tarafı yüksek çözünürlüklü Canvas yedek desteği.

### Neden Bu Yığın?
1. **Tam cPanel / Paylaşımlı Hosting Uyumu:** Node.js, Redis, Supervisor veya Imagick gibi paylaşımlı hostinglerde bulunmayan servislere sıfır bağımlılık.
2. **Yüksek Performans & SEO:** SSR (Server-Side Rendering) Blade çıktıları sayesinde arama motorları tüm HTML, JSON-LD ve meta etiketlerini anında indeksler; QR menü milisaniyeler içinde açılır.
3. **Güvenlik:** Laravel'in yerleşik CSRF, XSS, SQL Injection (PDO binding), Rate Limiter, Bcrypt/Argon2 parola hashleme ve Policy tabanlı erişim denetimi.

---

## 3. Veritabanı Şeması (Tablolar ve İlişkiler)

Tüm tablo ve sütun adları İngilizce, arayüz ve doğrulama mesajları Türkçedir.

### 3.1. Kimlik Doğrulama, Yetki (RBAC) ve Şube Kapsamı
1. **`users`**
   - `id`, `name`, `email` (unique), `phone`, `password`, `is_active` (boolean, default true), `all_branches` (boolean, default false), `last_login_at`, `last_login_ip`, `remember_token`, `timestamps`
2. **`roles`**
   - `id`, `name` (örn: "Süper Admin"), `slug` (unique: `super_admin`, `franchise_manager`, `product_manager`, `price_manager`, `branch_manager`, `feedback_manager`), `description`, `is_system` (boolean - sistem rolleri silinemez), `timestamps`
3. **`permissions`**
   - `id`, `name`, `slug` (unique, örn: `branches.view`, `branches.manage`, `products.manage`, `categories.manage`, `prices.bulk_update`, `prices.branch_update`, `branch_menu.manage`, `tables.manage`, `feedbacks.view`, `feedbacks.manage`, `franchises.view`, `franchises.manage`, `settings.manage`, `users.manage`, `roles.manage`, `audit_logs.view`, `excel.import`, `excel.export`), `group_name`, `timestamps`
4. **`role_user`** (Pivot - Çoklu Rol Desteği)
   - `user_id`, `role_id`
5. **`permission_role`** (Pivot)
   - `permission_id`, `role_id`
6. **`branch_user`** (Pivot - Kullanıcı Şube Kapsamı / Branch Scope)
   - `branch_id`, `user_id`

### 3.2. Şubeler ve Çalışma Saatleri
7. **`branches`**
   - `id`, `name`, `slug` (unique, Türkçe karaktersiz), `city`, `district`, `address`, `latitude` (decimal 10,7), `longitude` (decimal 10,7), `phone`, `whatsapp`, `email`, `instagram_url`, `facebook_url`, `google_maps_url`, `cover_image`, `gallery_images` (JSON), `description`, `is_active` (boolean), `sort_order` (int), `seo_title`, `seo_description`, `seo_og_image`, `timestamps`
8. **`branch_hours`**
   - `id`, `branch_id`, `day_of_week` (0=Pazartesi .. 6=Pazar), `open_time` (time), `close_time` (time), `is_closed` (boolean), `timestamps`
9. **`branch_special_days`** (Özel gün / tatil kapatma veya özel saat)
   - `id`, `branch_id`, `date` (date), `is_closed` (boolean), `open_time` (nullable), `close_time` (nullable), `note` (örn: "Bayram Tatili"), `timestamps`

### 3.3. Kategori, Ürün Havuzu ve Şube Menüsü / Fiyat Hiyerarşisi
10. **`categories`**
    - `id`, `name`, `slug` (unique), `description`, `image`, `icon`, `sort_order` (int), `is_active` (boolean), `timestamps`
11. **`products`** (Yalnızca Merkez Tarafından Yönetilen Ürün Havuzu)
    - `id`, `category_id`, `sku` (unique, Excel upsert için anahtar), `name`, `slug` (unique), `short_description`, `description`, `image`, `base_price` (decimal 10,2 - Merkez varsayılan fiyatı), `min_price` (nullable decimal 10,2 - Şube alt sınırı), `max_price` (nullable decimal 10,2 - Şube üst sınırı), `tags` (JSON: `["vegan", "vegetarian", "lactose_free", "sugar_free", "gluten_free", "new", "signature"]`), `temperature_options` (JSON: `["hot", "cold"]`), `variants` (JSON: `[{"name": "Tall", "price_diff": 0}, {"name": "Grande", "price_diff": 15}]`), `Origin / Bean Info` (`bean_origin`, `roast_level`, `tasting_notes` - `/urunlerimiz` sayfasında da sergilenebilmesi için), `is_featured` (boolean), `is_active` (boolean), `sort_order` (int), `timestamps`
12. **`branch_product`** (Şube Menüsü, Şube Özel Fiyatı ve Şube İndirimi)
    - `id`, `branch_id`, `product_id`, `is_available` (boolean - şube menüsünde aktif mi), `is_sold_out` (boolean - "Tükendi" işareti), `custom_price` (nullable decimal 10,2 - Şube override fiyatı), `discount_type` (nullable enum: `percentage`, `fixed`), `discount_value` (nullable decimal 10,2), `discount_starts_at` (nullable datetime), `discount_ends_at` (nullable datetime), `sort_order` (int), `timestamps`
    - *Unique index:* `(branch_id, product_id)`

### 3.4. Masalar ve Müşteri Geri Bildirimleri
13. **`dining_tables`**
    - `id`, `branch_id`, `name` (örn: "Bahçe 4", "Salon 12"), `table_number` (string), `token` (unique, 32 karakter kriptografik tahmin edilemez token), `is_active` (boolean), `sort_order` (int), `timestamps`
14. **`feedbacks`** (İstek / Öneri / Şikayet)
    - `id`, `branch_id`, `dining_table_id` (nullable), `type` (enum: `request` [İstek], `suggestion` [Öneri], `complaint` [Şikayet]), `message` (text), `customer_name` (nullable - zorunlu değil), `customer_contact` (nullable - zorunlu değil), `status` (enum: `new` [Yeni], `in_review` [İnceleniyor], `resolved` [Çözüldü]), `internal_note` (nullable text), `submitted_date` (date), `submitted_time` (time), `day_name` (string - örn: "Cumartesi"), `ip_address` (string), `user_agent` (string), `timestamps`

### 3.5. Franchise Başvuruları, Kurumsal İçerik (CMS) ve Denetim Kaydı (Audit Log)
15. **`franchise_applications`**
    - `id`, `full_name`, `phone`, `email`, `city`, `district`, `investment_budget` (string), `preferred_location_note` (nullable), `message` (text), `kvkk_accepted` (boolean), `status` (enum: `new` [Yeni], `called` [Arandı], `interviewed` [Görüşüldü], `positive` [Olumlu], `negative` [Olumsuz]), `internal_notes` (nullable text), `ip_address`, `timestamps`
16. **`press_items`** (`/basinda-biz` içerikleri)
    - `id`, `title`, `slug` (unique), `publisher` (yayın kuruluşu), `published_at` (date), `summary`, `content` (text), `external_url` (nullable), `image` (nullable), `is_active` (boolean), `sort_order` (int), `timestamps`
17. **`site_settings`** (Key-Value + Grup tabanlı dinamik CMS & Tema ayarları)
    - `id`, `group` (`general`, `theme`, `home`, `about`, `contact`, `franchise`, `footer`, `social`), `key` (unique), `value` (longtext), `type` (`text`, `textarea`, `image`, `color`, `json`, `boolean`), `timestamps`
18. **`seo_pages`** (Sayfa bazlı dinamik SEO yönetimi)
    - `id`, `route_name` (unique: `home`, `about`, `products`, `branches`, `franchise`, `press`, `contact`), `page_label`, `meta_title`, `meta_description`, `og_title`, `og_description`, `og_image`, `canonical_url`, `noindex` (boolean), `timestamps`
19. **`audit_logs`** (Değiştirilemez ve Silinemez Denetim Kaydı)
    - `id`, `user_id` (nullable), `user_name` (kullanıcı silinse bile kaybolmaması için), `branch_id` (nullable), `action_type` (örn: `price.updated`, `price.bulk_updated`, `discount.updated`, `branch_menu.toggled`, `product.created`, `user.updated`, `role.updated`, `settings.updated`), `auditable_type`, `auditable_id`, `description`, `old_values` (JSON), `new_values` (JSON), `ip_address`, `user_agent`, `created_at` (updated_at yok, modelde `delete()` ve `update()` engelli).

---

## 4. Rota (URL) Yapısı

### 4.1. Herkese Açık (Kurumsal & QR Menü) Rotalar
| HTTP | URI | Controller / Action | Açıklama |
|---|---|---|---|
| `GET` | `/` | `PublicController@home` | Kurumsal ana sayfa |
| `GET` | `/hakkimizda` | `PublicController@about` | Biz Kimiz / Marka hikayesi |
| `GET` | `/urunlerimiz` | `PublicController@products` | Kahve çekirdekleri ve ürün tanıtımı |
| `GET` | `/subelerimiz` | `PublicController@branches` | Şube listesi + interaktif harita (Leaflet/OSM) |
| `GET` | `/subelerimiz/{branch:slug}` | `PublicController@branchShow` | Şube detay sayfası (adres, harita, saatler, menü linki) |
| `GET` | `/qrmenu/{branch:slug}` | `QrMenuController@show` | Şubenin herkese açık QR menüsü (masa: belirtilmedi) |
| `GET` | `/qrmenu/{branch:slug}/m/{token}` | `QrMenuController@showTable` | Masaya özel QR menü (`noindex`, canonical: `/qrmenu/{slug}`) |
| `POST`| `/qrmenu/{branch:slug}/geri-bildirim`| `QrMenuController@storeFeedback` | İstek / Öneri / Şikayet gönderimi (Rate limit + Honeypot) |
| `GET` | `/franchise` | `PublicController@franchise` | Franchise bilgilendirme ve başvuru formu |
| `POST`| `/franchise` | `PublicController@storeFranchise` | Franchise başvurusu kaydetme (Rate limit + Honeypot) |
| `GET` | `/basinda-biz` | `PublicController@press` | Basında biz listesi ve detayları |
| `GET` | `/iletisim` | `PublicController@contact` | Genel merkez iletişim bilgileri |
| `GET` | `/sitemap.xml` | `SeoController@sitemap` | Dinamik XML Sitemap (şubeler ve QR menüler dahil) |
| `GET` | `/robots.txt` | `SeoController@robots` | Dinamik `robots.txt` (`/yonetim` ve `/qrmenu/*/m/*` Disallow) |

### 4.2. Yönetim Paneli (`/yonetim`) Rotaları
- **Kimlik Doğrulama:**
  - `GET|POST /yonetim` (Tek giriş ekranı, rol bazlı akıllı yönlendirme, rate limit)
  - `POST /yonetim/cikis` (Çıkış)
  - `GET|POST /yonetim/sifremi-unuttum` ve `GET|POST /yonetim/sifre-sifirla/{token}`
  - `GET|PUT /yonetim/profil` (Şifre değiştirme ve hesap bilgileri)
- **Merkez & Ortak Yönetim Modülleri (`/yonetim/...`):**
  - `/yonetim/panel` — Rol ve şube kapsamına göre özelleştirilmiş özet ekranı
  - `/yonetim/subeler` — Şube CRUD, çalışma saatleri, özel günler
  - `/yonetim/kategoriler` — Kategori CRUD + sürükle-bırak sıralama (`POST /yonetim/kategoriler/sirala`)
  - `/yonetim/urunler` — Ürün havuzu CRUD (yalnızca merkez)
  - `/yonetim/fiyat-yonetimi` — Toplu fiyat güncelleme, önizleme (`POST /yonetim/fiyat-yonetimi/onizleme`), onay ve uygulama
  - `/yonetim/excel` — Excel şablon indirme, ürün içe aktarma (doğrulama + önizleme + upsert), dışa aktarma uçları
  - `/yonetim/sube/{branch}/menu` — Şube menü seçimi (ekle/çıkar, tükendi işaretle, şube fiyatı ve tarihli indirim)
  - `/yonetim/sube/{branch}/masalar` — Masa CRUD, benzersiz token üretimi, Masa Kartı Üretici (3 şablon, SVG/PNG/PDF tekli ve toplu çıktı)
  - `/yonetim/sube/{branch}/ayarlar` — Şube yöneticisi için kendi şubesinin saat, telefon, adres, sosyal medya yönetimi
  - `/yonetim/geri-bildirimler` — İstek/Öneri/Şikayet listesi, filtreleme, durum/iç not güncelleme, Excel çıktısı (Şube kapsamlı)
  - `/yonetim/franchise-basvurulari` — Franchise başvuruları, durum/not takibi, Excel çıktısı
  - `/yonetim/icerik-yonetimi` — Site ayarları, renk/logo, ana sayfa, hakkımızda, basın, iletişim ve sayfa bazlı SEO yönetimi
  - `/yonetim/kullanicilar` ve `/yonetim/roller` — Çoklu rol atama, şube bağlama, yeni rol ve izin matrisi yönetimi (Süper Admin)
  - `/yonetim/denetim-kayitlari` — Değiştirilemez audit log listesi, filtreleme ve Excel çıktısı (Süper Admin)

---

## 5. Rol ve Yetki Matrisi (RBAC + Şube Kapsamı)

| Modül / Yetki (`permission`) | Süper Admin | Franchise / Satış | Ürün / Menü Yön. | Fiyat Yöneticisi | Şube Yöneticisi | Geri Bildirim Sor. |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| Şube Yönetimi (Merkez CRUD) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Kendi Şube Bilgisi & Saatleri | ✅ (Tümü) | ❌ | ❌ | ❌ | ✅ (Kendi Şubesi) | ❌ |
| Ürün Havuzu & Kategori (Merkez) | ✅ | ❌ | ✅ | ❌ | ❌ (Sadece Seçer) | ❌ |
| Şube Menü Seçimi & "Tükendi" | ✅ (Tümü) | ❌ | ✅ (Tümü) | ❌ | ✅ (Kendi Şubesi) | ❌ |
| Toplu Fiyat & Şube Fiyat Yönetimi| ✅ (Tümü) | ❌ | ❌ | ✅ (Tümü/Seçili) | ✅ (Sadece Kendi) | ❌ |
| Masa Yönetimi & Masa Kartı | ✅ (Tümü) | ❌ | ❌ | ❌ | ✅ (Kendi Şubesi) | ❌ |
| Geri Bildirimler (İstek/Öneri/Şikayet)| ✅ (Tümü) | ❌ | ❌ | ❌ | ✅ (Kendi Şubesi) | ✅ (Tümü/Kapsam) |
| Franchise Başvuruları | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Excel İçe/Dışa Aktarma | ✅ (Tümü) | ✅ (Franchise) | ✅ (Ürün) | ✅ (Fiyat) | ❌ | ✅ (Geri Bildirim)|
| Site / CMS / SEO Ayarları | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Kullanıcı & Rol Yönetimi | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Denetim Kayıtları (Audit Logs) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

*Not:* Bir kullanıcıya birden fazla rol atanabilir (izinler birleşir - union). Şube kapsamı (`branch_user` veya `all_branches`) Policy katmanında her sorguda ve işlemde zorunlu olarak denetlenir.

---

## 6. Fiyat Hesaplama Zinciri (`PriceCalculationService`)

1. **Merkez Fiyatı (`products.base_price`):** Varsayılan başlangıç fiyatı.
2. **Şube Fiyatı (`branch_product.custom_price`):** Tanımlıysa (`!= null`), merkez fiyatını ezer (override).
3. **Şube İndirimi (`branch_product.discount_type`, `discount_value`, `discount_starts_at`, `discount_ends_at`):**
   - Şu anki zaman başlangıç ve bitiş tarihleri arasındaysa (veya tarih sınırı yoksa) indirim aktiftir.
   - `percentage` ise: `etkin_baz_fiyat * (1 - discount_value / 100)`
   - `fixed` ise: `max(0, etkin_baz_fiyat - discount_value)`
4. **Çıktı DTO / Dizi:**
   - `base_price` (Merkez fiyatı)
   - `branch_price` (Şube fiyatı veya merkez fiyatı)
   - `final_price` (Müşterinin gördüğü son fiyat)
   - `has_discount` (boolean - üstü çizili eski fiyat gösterimi için)
   - `discount_label` (örn: `%15 İndirim` veya `20 ₺ İndirim`)

---

## 7. Geliştirme Fazları

- **Faz 1: Altyapı, Kurulum, Marka Varlıkları ve Veritabanı**
  - Composer & Laravel 11 kurulumu, gerekli paketlerin (`phpspreadsheet`, `dompdf`, `php-qrcode`) eklenmesi.
  - `logo/` varlıklarının `public/brand/` altına taşınması, self-hosted Türkçe `.woff2` fontların kurulması.
  - Tüm migration'ların, modellerin, ilişkilerin ve `AuditLog` (silinemez/değiştirilemez) altyapısının oluşturulması.
  - Temiz Veri Politikasına uygun `DatabaseSeeder` ve `php artisan soohouse:install` komutu (Sadece roller, izinler, boş/varsayılan site ve SEO ayarları, `.env`'den alınan Süper Admin hesabı — **sıfır sahte veri**).
- **Faz 2: Kimlik Doğrulama, RBAC, Fiyat Servisi ve Audit Log Servisi**
  - `/yonetim` giriş ekranı, rate limiting, şifre sıfırlama, aktiflik kontrolü, rol bazlı yönlendirme.
  - Çoklu rol ve şube kapsamı (Branch Scope) Policy/Gate denetimleri.
  - `PriceCalculationService` ve `AuditLogger` servis sınıfları.
- **Faz 3: Genel Merkez ve Şube Yönetim Panelleri**
  - Şube, Kategori (sürükle-bırak), Ürün Havuzu, Toplu Fiyat Güncelleme (önizleme + ez/ezme + onay).
  - Şube Paneli: Menü seçimi, tükendi işaretleme, şube fiyatı/indirimi (min/maks sınır kontrolü), çalışma saatleri.
  - Masa Yönetimi ve Masa Kartı Üretici (3 farklı marka şablonu, SVG / PNG / PDF tekli ve toplu baskı çıktısı).
  - Geri Bildirim, Franchise Başvuruları, CMS/SEO Ayarları, Kullanıcı/Rol Yönetimi, Denetim Kayıtları ve Excel İçe/Dışa Aktarma.
- **Faz 4: Kurumsal Web Sitesi, QR Menü ve SEO**
  - "Mükemmelin mükemmeli" Soohouse Coffee kurumsal sayfaları (`/`, `/hakkimizda`, `/urunlerimiz`, `/subelerimiz`, `/subelerimiz/{slug}`, `/franchise`, `/basinda-biz`, `/iletisim`), şık boş durum (empty state) ekranları.
  - Mobil öncelikli, ultra hızlı QR Menü (`/qrmenu/{slug}` ve `/qrmenu/{slug}/m/{token}`) + İstek/Öneri/Şikayet modülü.
  - JSON-LD yapılandırılmış veriler, dinamik `sitemap.xml`, `robots.txt`, `.htaccess` performans/güvenlik başlıkları, özel 404/500 sayfaları.
- **Faz 5: Otomatik Testler, Kalite Kontrol ve Dokümantasyon (`README.md`)**
  - PHPUnit / Pest testleri: RBAC matrisi, şube veri izolasyonu, fiyat hesaplama zinciri, toplu fiyat güncelleme, audit log değişmezliği, geri bildirim (şube/masa/zaman) kaydı.
  - Frontend derlemesi (`npm run build`), `README.md` ve `.env.example` teslimi.
