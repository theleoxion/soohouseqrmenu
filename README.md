# SOOHOUSE COFFEE® — Kurumsal Web Platformu, Merkezi Yönetim, Şube Paneli ve QR Menü Sistemi

Bu proje; **Soohouse Coffee®** markası için sıfırdan geliştirilmiş, paylaşımlı hosting (**cPanel**) ile %100 uyumlu, çok şubeli kurumsal web sitesi, genel merkez yönetim paneli, şube yönetim paneli, masa kartı üretici (SVG/PNG/PDF) ve dijital QR menü platformudur.

---

## 1. Öne Çıkan Özellikler ve Mimari

- **Paylaşımlı Hosting (cPanel) Uyumlu:** Üretim ortamında Node.js, Redis, Supervisor veya Imagick gerektirmez. Frontend varlıkları (`public/build/`) ve Türkçe karakter destekli `.woff2` fontlar (`public/fonts/`) yerel olarak derlenip projeye dahil edilmiştir.
- **Marka Kimliği:** `logo/` klasöründeki orijinal dosyalara dokunulmadan optimize edilen SVG, WebP ve PNG marka varlıkları `public/brand/` dizininde sunulmaktadır.
- **Rol Tabanlı Yetki (RBAC) + Şube Kapsamı (Branch Scope):**
  - Bir kullanıcıya **birden fazla rol** atanabilir (`role_user`).
  - Kullanıcılar belirli şubelere bağlanabilir (`branch_user`) veya tüm şubelere yetkilendirilebilir (`all_branches`).
  - Varsayılan Roller: **Süper Admin**, **Franchise / Satış Birimi**, **Ürün / Menü Yöneticisi**, **Fiyat Yöneticisi**, **Şube Yöneticisi**, **Geri Bildirim Sorumlusu**.
- **Fiyat Hiyerarşisi (`PriceCalculationService`):**
  - `Merkez Fiyatı (base_price)` → `Şube Özel Fiyatı (custom_price override)` → `Şube Tarihli/Oranlı İndirimi`.
  - Toplu fiyat güncellemede şubelerin özel fiyatlarını **ezme / koruma** seçeneği ve işlem öncesi **Eski → Yeni Fiyat Önizleme + Onay** ekranı.
- **Değiştirilemez Denetim Kaydı (`AuditLog`):**
  - Fiyat, indirim, menü, şube, kullanıcı, rol ve site ayarı değişiklikleri eski ve yeni değerleriyle kaydedilir. Model katmanında güncelleme ve silme işlemleri kilitlenmiştir (`LogicException`).
- **QR Menü, Masa Yönetimi ve Masa Kartı Üretici (`TableCardService`):**
  - Her masa için tahmin edilemez 32 karakterlik kriptografik token üretilir (`/qrmenu/{sube-slug}/m/{masa-token}`).
  - **SVG, PNG ve PDF** (tekli veya tüm masalar tek PDF'te toplu baskı) masa kartı üretimi. Sunucu tarafında Imagick gerektirmez; saf SVG, PHP `GD` ve `dompdf` kullanır.
- **Excel İçe / Dışa Aktarma (`ExcelService` - PhpSpreadsheet):**
  - Ürün havuzu toplu yükleme şablonu, yükleme öncesi doğrulama/önizleme, satır bazlı hata raporu ve SKU bazlı **upsert** (ekleme/güncelleme).
  - Ürün havuzu, şube fiyat listesi, franchise başvuruları, müşteri geri bildirimleri ve denetim kayıtları için Türkçe karakter ve `₺` para birimi biçimli `.xlsx` çıktıları.
- **Temiz Veri Politikası:**
  - Veritabanında hiçbir sahte/demo şube, ürün, başvuru veya geri bildirim bulunmaz. Veri girilene kadar tüm sayfalar özel tasarlanmış boş durum (empty state) ekranları gösterir.

---

## 2. Yerel Kurulum Adımları

### Gereksinimler
- PHP 8.2 veya üzeri (`pdo_mysql` veya `pdo_sqlite`, `gd`, `mbstring`, `xml`, `zip`, `fileinfo`, `intl` uzantıları aktif)
- Composer 2.x
- MySQL 8.0+ / MariaDB 10.6+ (veya yerel geliştirme için SQLite)

### Kurulum Komutları

```bash
# 1. Bağımlılıkları yükleyin
composer install

# 2. Ortam dosyasını oluşturun ve uygulama anahtarını üretin
cp .env.example .env
php artisan key:generate

# 3. .env dosyasındaki veritabanı (DB_*) ve APP_URL ayarlarını düzenleyin

# 4. Veritabanı tablolarını oluşturun
php artisan migrate

# 5. Varsayılan rolleri, izinleri, temel ayarları ve Süper Admin hesabını oluşturun
php artisan soohouse:install --email="admin@soohousecoffee.com" --password="GucluSifre123!" --name="Soohouse Süper Admin"
```

> **Not:** Frontend derlemesi (`public/build/`) halihazırda projeye dahildir. Stil veya JS dosyalarında değişiklik yaparsanız `npm install && npm run build` komutunu çalıştırabilirsiniz.

---

## 3. Paylaşımlı Hostinge (cPanel) Yükleme Rehberi

### 3.1. Dosya Yapısı ve `public_html` Ayarı
Paylaşımlı hostingde güvenlik için uygulama çekirdek dosyalarının `public_html` dışında tutulması önerilir:

1. Sunucunuzun ana dizininde (`/home/kullaniciadi/`) `soohouse_app` adlı bir klasör oluşturun ve (`vendor/` ile `public/build/` dahil) tüm proje dosyalarını buraya yükleyin (`composer install --no-dev --optimize-autoloader` çıktısıyla).
2. **Yöntem A (Önerilen - Sembolik Bağ / Document Root):**
   - Alan adınızın kök dizinini (Document Root) cPanel'den `/home/kullaniciadi/soohouse_app/public` olarak ayarlayın **VEYA**
   - Mevcut `public_html` klasörünü yedekleyip silin ve SSH/Terminal üzerinden sembolik bağ oluşturun:
     ```bash
     ln -s /home/kullaniciadi/soohouse_app/public /home/kullaniciadi/public_html
     ```
3. **Yöntem B (`public` içeriğini `public_html`'e taşıma):**
   - `public/` klasörü içindeki tüm dosyaları (`.htaccess`, `index.php`, `build/`, `brand/`, `fonts/`) doğrudan `public_html/` içine kopyalayın.
   - `public_html/index.php` içindeki `require` ve `bootstrap` yollarını `../soohouse_app/vendor/autoload.php` ve `../soohouse_app/bootstrap/app.php` olacak şekilde güncelleyin.

### 3.2. `.env` ve Veritabanı Yapılandırması
1. cPanel → **MySQL® Veritabanları** bölümünden yeni bir veritabanı ve kullanıcı oluşturup tüm yetkileri (ALL PRIVILEGES) verin.
2. `.env` dosyasını düzenleyin:
   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://alanadiniz.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=cpanel_veritabani_adi
   DB_USERNAME=cpanel_kullanici_adi
   DB_PASSWORD=cpanel_sifresi
   ```

### 3.3. Klasör İzinleri ve Storage Bağlantısı
`storage/` ve `bootstrap/cache/` ile `public/uploads/` dizinlerinin web sunucusu tarafından yazılabilir olduğundan emin olun:

```bash
chmod -R 775 storage bootstrap/cache public/uploads
php artisan storage:link
php artisan migrate --force
php artisan soohouse:install --email="yonetim@alanadiniz.com" --password="GucluSifre123!"
php artisan optimize
```

### 3.4. Cron Job (Zamanlanmış Görev) Tanımı
cPanel → **Cron İşleri (Cron Jobs)** menüsünden dakikada bir çalışan aşağıdaki komutu ekleyin:

```bash
* * * * * /usr/local/bin/php /home/kullaniciadi/soohouse_app/artisan schedule:run >> /dev/null 2>&1
```

---

## 4. QR ve Masa Kartı Üretim Yöntemi

Hosting ortamlarında `Imagick` uzantısının bulunmama ihtimaline karşı masa kartı ve QR üretimi üç katmanlı olarak tasarlanmıştır:
1. **QR Kod (`chillerlan/php-qrcode`):** Saf PHP ile yüksek hata düzeltme seviyesinde (`ECC Level H`) vektörel SVG ve matris verisi üretir.
2. **SVG Çıktısı:** Vektörel baskı (matbaa/Corel/Illustrator) için doğrudan ölçeklenebilir SVG masa kartı üretir.
3. **PNG Çıktısı:** Standart PHP `gd` uzantısı ile 900×1260 piksel yüksek çözünürlüklü PNG masa kartı üretir.
4. **PDF Çıktısı (`dompdf/dompdf`):** Tek masa veya şubedeki tüm aktif masaları tek bir A4 PDF dosyasında yan yana kesime uygun şekilde dışa aktarır.

---

## 5. Yedekleme ve Geri Yükleme (Backup & Restore)

### Yedekleme (Backup)
1. **Veritabanı Yedeği:**
   cPanel → **phpMyAdmin** veya **Backup Wizard** üzerinden ya da SSH ile:
   ```bash
   mysqldump -u kullanici_adi -p veritabani_adi > soohouse_db_$(date +%F).sql
   ```
   *(Not: `audit_logs` tablosu değiştirilemez denetim kayıtlarını barındırdığı için düzenli yedeklere mutlaka dahil edilmelidir.)*
2. **Yüklenen Medya Dosyaları:**
   `public/uploads/` klasörünü (şube, ürün, kategori ve CMS görselleri) ve `.env` dosyasını yedekleyin:
   ```bash
   tar -czf soohouse_uploads_$(date +%F).tar.gz public/uploads .env
   ```

### Geri Yükleme (Restore)
1. Veritabanı yedeğini geri yükleyin:
   ```bash
   mysql -u kullanici_adi -p veritabani_adi < soohouse_db_YYYY-MM-DD.sql
   ```
2. `public/uploads/` arşivini açın ve önbelleği temizleyin:
   ```bash
   tar -xzf soohouse_uploads_YYYY-MM-DD.tar.gz
   php artisan optimize:clear
   ```

---

## 6. Sık Karşılaşılan Sorunlar ve Çözümleri

- **"500 Internal Server Error" hatası alıyorum:**
  - `storage/` ve `bootstrap/cache/` klasör izinlerinin `775` olduğundan ve `.env` içinde `APP_KEY` değerinin dolu olduğundan emin olun.
- **Stil (CSS) veya fontlar yüklenmiyor:**
  - `.env` içindeki `APP_URL` değerinin sitenizin gerçek adresi (`https://...`) ile eşleştiğini ve `public/build/manifest.json` dosyasının sunucuda mevcut olduğunu doğrulayın.
- **Excel yüklerken dosya boyutu hatası alıyorum:**
  - cPanel → **Select PHP Version → Options** menüsünden `upload_max_filesize` ve `post_max_size` değerlerini `16M` veya üzerine çıkarın.

---

## 7. Otomatik Testleri Çalıştırma

Yetki/rol matrisi, şube veri izolasyonu, fiyat hesaplama zinciri, toplu fiyat güncelleme, değiştirilemez audit log ve QR menü geri bildirim testlerini çalıştırmak için:

```bash
php artisan test --compact
```
