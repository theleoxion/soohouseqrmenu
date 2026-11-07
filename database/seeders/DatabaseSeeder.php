<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SeoPage;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed only default roles, permissions, base site settings, SEO entries, and Super Admin account.
     * STRICT DATA POLICY: No fake/demo branches, products, categories, applications, or feedbacks!
     */
    public function run(): void
    {
        $this->seedPermissionsAndRoles();
        $this->seedSiteSettings();
        $this->seedSeoPages();
        $this->seedSuperAdmin();
    }

    protected function seedPermissionsAndRoles(): void
    {
        $permissions = [
            // Şube Yönetimi
            ['slug' => 'branches.view', 'name' => 'Şubeleri Görüntüleme', 'group_name' => 'Şube Yönetimi'],
            ['slug' => 'branches.manage', 'name' => 'Merkez Şube Ekleme / Düzenleme / Pasife Alma', 'group_name' => 'Şube Yönetimi'],
            ['slug' => 'branch_settings.manage', 'name' => 'Bağlı Şube Bilgileri ve Çalışma Saatlerini Yönetme', 'group_name' => 'Şube Yönetimi'],

            // Ürün ve Kategori Havuzu
            ['slug' => 'categories.manage', 'name' => 'Merkez Kategori Yönetimi ve Sıralama', 'group_name' => 'Ürün & Menü Havuzu'],
            ['slug' => 'products.manage', 'name' => 'Merkez Ürün Havuzu Yönetimi', 'group_name' => 'Ürün & Menü Havuzu'],
            ['slug' => 'branch_menu.manage', 'name' => 'Şube Menüsüne Ürün Ekleme/Çıkarma ve Tükendi İşaretleme', 'group_name' => 'Ürün & Menü Havuzu'],

            // Fiyatlandırma
            ['slug' => 'prices.bulk_update', 'name' => 'Merkez Toplu Fiyat Güncelleme', 'group_name' => 'Fiyat Yönetimi'],
            ['slug' => 'prices.branch_update', 'name' => 'Şube Özel Fiyatı ve İndirim Tanımlama', 'group_name' => 'Fiyat Yönetimi'],

            // Masa ve QR Kart
            ['slug' => 'tables.manage', 'name' => 'Masa Yönetimi ve Masa Kartı (QR) Üretimi', 'group_name' => 'QR Menü & Masa'],

            // Geri Bildirimler
            ['slug' => 'feedbacks.view', 'name' => 'Müşteri İstek/Öneri/Şikayetlerini Görüntüleme', 'group_name' => 'Geri Bildirim Yönetimi'],
            ['slug' => 'feedbacks.manage', 'name' => 'Müşteri Geri Bildirim Durum ve Not Yönetimi', 'group_name' => 'Geri Bildirim Yönetimi'],

            // Franchise
            ['slug' => 'franchises.view', 'name' => 'Franchise Başvurularını Görüntüleme', 'group_name' => 'Franchise / Satış'],
            ['slug' => 'franchises.manage', 'name' => 'Franchise Başvuru Durum ve Not Yönetimi', 'group_name' => 'Franchise / Satış'],

            // Excel
            ['slug' => 'excel.import', 'name' => 'Excel ile Toplu Ürün İçe Aktarma', 'group_name' => 'Excel İşlemleri'],
            ['slug' => 'excel.export', 'name' => 'Excel Dışa Aktarma İşlemleri', 'group_name' => 'Excel İşlemleri'],

            // Sistem & Süper Admin
            ['slug' => 'settings.manage', 'name' => 'Site, Tema, CMS ve SEO Ayarlarını Yönetme', 'group_name' => 'Sistem Yönetimi'],
            ['slug' => 'users.manage', 'name' => 'Kullanıcı ve Şube Atama Yönetimi', 'group_name' => 'Sistem Yönetimi'],
            ['slug' => 'roles.manage', 'name' => 'Rol ve İzin Matrisi Yönetimi', 'group_name' => 'Sistem Yönetimi'],
            ['slug' => 'audit_logs.view', 'name' => 'Denetim Kayıtlarını (Audit Logs) Görüntüleme', 'group_name' => 'Sistem Yönetimi'],
        ];

        $permissionModels = [];
        foreach ($permissions as $perm) {
            $permissionModels[$perm['slug']] = Permission::updateOrCreate(
                ['slug' => $perm['slug']],
                ['name' => $perm['name'], 'group_name' => $perm['group_name']]
            );
        }

        $roles = [
            [
                'slug' => 'super_admin',
                'name' => 'Süper Admin',
                'description' => 'Tüm şubelere, ayarlara, kullanıcılara, rollere ve denetim kayıtlarına tam erişim.',
                'is_system' => true,
                'permissions' => array_keys($permissionModels),
            ],
            [
                'slug' => 'franchise_manager',
                'name' => 'Franchise / Satış Birimi',
                'description' => 'Sadece franchise başvurularını görüntüler, yönetir ve dışa aktarır.',
                'is_system' => true,
                'permissions' => ['franchises.view', 'franchises.manage', 'excel.export'],
            ],
            [
                'slug' => 'product_manager',
                'name' => 'Ürün / Menü Yöneticisi',
                'description' => 'Merkez ürün havuzunu ve kategorileri yönetir, Excel ile ürün aktarır.',
                'is_system' => true,
                'permissions' => ['categories.manage', 'products.manage', 'branch_menu.manage', 'excel.import', 'excel.export'],
            ],
            [
                'slug' => 'price_manager',
                'name' => 'Fiyat Yöneticisi',
                'description' => 'Toplu veya şube bazlı fiyatları ve indirimleri günceller.',
                'is_system' => true,
                'permissions' => ['prices.bulk_update', 'prices.branch_update', 'excel.export'],
            ],
            [
                'slug' => 'branch_manager',
                'name' => 'Şube Yöneticisi',
                'description' => 'Yalnızca bağlı olduğu şubelerin menü, fiyat/indirim, çalışma saatleri, masa ve geri bildirimlerini yönetir.',
                'is_system' => true,
                'permissions' => [
                    'branch_settings.manage',
                    'branch_menu.manage',
                    'prices.branch_update',
                    'tables.manage',
                    'feedbacks.view',
                    'feedbacks.manage',
                ],
            ],
            [
                'slug' => 'feedback_manager',
                'name' => 'Geri Bildirim Sorumlusu',
                'description' => 'Müşteri istek, öneri ve şikayetlerini görüntüler ve yönetir.',
                'is_system' => true,
                'permissions' => ['feedbacks.view', 'feedbacks.manage', 'excel.export'],
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'is_system' => $roleData['is_system'],
                ]
            );

            $permIds = collect($roleData['permissions'])
                ->map(fn (string $slug) => $permissionModels[$slug]->id)
                ->all();

            $role->permissions()->sync($permIds);
        }
    }

    protected function seedSiteSettings(): void
    {
        $defaults = [
            // Genel & Tema
            ['group' => 'general', 'key' => 'brand_name', 'value' => 'Soohouse Coffee', 'type' => 'text', 'label' => 'Marka Adı'],
            ['group' => 'general', 'key' => 'brand_slogan', 'value' => 'Topraktan Fincana Nitelikli Kahve Deneyimi', 'type' => 'text', 'label' => 'Marka Sloganı'],
            ['group' => 'theme', 'key' => 'logo_light', 'value' => '/brand/soohouse-logo.svg', 'type' => 'image', 'label' => 'Ana Logo (Açık Zemin)'],
            ['group' => 'theme', 'key' => 'logo_icon', 'value' => '/brand/soohouse-icon.svg', 'type' => 'image', 'label' => 'Amblem / Traktör İkonu'],
            ['group' => 'theme', 'key' => 'favicon', 'value' => '/favicon.ico', 'type' => 'image', 'label' => 'Favicon'],
            ['group' => 'theme', 'key' => 'primary_color', 'value' => '#0D0D0C', 'type' => 'color', 'label' => 'Ana Marka Rengi (Obsidian Siyah)'],
            ['group' => 'theme', 'key' => 'accent_color', 'value' => '#C87D3B', 'type' => 'color', 'label' => 'Vurgu Rengi (Hasat Kehribarı)'],
            ['group' => 'theme', 'key' => 'bg_color', 'value' => '#F8F5F0', 'type' => 'color', 'label' => 'Açık Zemin Rengi (Krem)'],

            // Ana Sayfa Bölümleri (Gerçek marka konumlandırmasına dayalı, uydurma veri içermez)
            ['group' => 'home', 'key' => 'hero_badge', 'value' => 'SOOHOUSE COFFEE® — NİTELİKLİ KAHVE & FRANCHISE PLATFORMU', 'type' => 'text', 'label' => 'Hero Üst Rozet'],
            ['group' => 'home', 'key' => 'hero_title', 'value' => 'Toprağın Emeği, Kusursuz Kavrum.', 'type' => 'text', 'label' => 'Hero Ana Başlık'],
            ['group' => 'home', 'key' => 'hero_subtitle', 'value' => 'Soohouse Coffee olarak seçkin kahve çiftliklerinden özenle hasat edilen nitelikli çekirdekleri, kendine özgü kavrum profilimiz ve güçlü şube ekosistemimizle fincanınıza taşıyoruz.', 'type' => 'textarea', 'label' => 'Hero Alt Açıklama'],
            ['group' => 'home', 'key' => 'Bean_section_title', 'value' => 'Çiftlikten Fincana İzlenebilir Kalite', 'type' => 'text', 'label' => 'Kahve Felsefesi Başlığı'],
            ['group' => 'home', 'key' => 'Bean_section_text', 'value' => 'Amblemimizdeki traktör, kahvenin kaynağındaki tarımsal emeğe ve toprağa duyduğumuz saygıyı simgeler. Her fincanda aynı standardı yakalamak için çekirdek seçiminden demleme reçetelerine kadar tüm süreçleri merkezden yönetiyoruz.', 'type' => 'textarea', 'label' => 'Kahve Felsefesi Metni'],

            // Hakkımızda (Biz Kimiz)
            ['group' => 'about', 'key' => 'about_title', 'value' => 'Topraktan İlham Alan Kahve Kültürü', 'type' => 'text', 'label' => 'Hakkımızda Ana Başlık'],
            ['group' => 'about', 'key' => 'about_lead', 'value' => 'Soohouse Coffee®, nitelikli kahveyi ulaşılabilir kılmak, üretim disipliniyle sürdürülebilir bir kafe deneyimi sunmak ve yatırımcılarıyla birlikte büyüyen güçlü bir franchise ağı kurmak amacıyla yola çıkmıştır.', 'type' => 'textarea', 'label' => 'Hakkımızda Özet Metni'],
            ['group' => 'about', 'key' => 'about_content', 'value' => "Markamızın kalbinde yer alan traktör sembolü; kahvenin bir endüstri ürünü olmadan önce toprağın, iklimin ve çiftçi emeğinin bir meyvesi olduğunu hatırlatır.\n\nMerkezi ürün geliştirme, standartlaştırılmış barista eğitimi ve şeffaf tedarik zinciri modelimizle tüm Soohouse Coffee şubelerinde misafirlerimize aynı yüksek kaliteyi sunuyoruz.", 'type' => 'textarea', 'label' => 'Hakkımızda Detay Metni'],
            ['group' => 'about', 'key' => 'about_vision', 'value' => 'Nitelikli kahve çekirdeklerini, özgün mimari konsept ve dijital operasyon gücüyle birleştirerek Türkiye genelinde referans gösterilen bir kafe zinciri olmak.', 'type' => 'textarea', 'label' => 'Vizyonumuz'],
            ['group' => 'about', 'key' => 'about_mission', 'value' => 'Misafirlerimize her şubede tutarlı, taze ve nitelikli lezzetler sunarken; iş ortaklarımıza kârlı, denetlenebilir ve uçtan uca desteklenen bir franchise modeli sağlamak.', 'type' => 'textarea', 'label' => 'Misyonumuz'],

            // Franchise
            ['group' => 'franchise', 'key' => 'franchise_title', 'value' => 'Soohouse Coffee® Ailesine Yatırımcı Olarak Katılın', 'type' => 'text', 'label' => 'Franchise Başlığı'],
            ['group' => 'franchise', 'key' => 'franchise_subtitle', 'value' => 'Lokasyon analizinden mimari projelendirmeye, ekipman tedarikinden barista akademisine ve merkezi dijital menü/fiyat yönetimine kadar tüm aşamalarda yanınızdayız.', 'type' => 'textarea', 'label' => 'Franchise Alt Açıklama'],
            ['group' => 'franchise', 'key' => 'kvkk_text', 'value' => '6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") kapsamında, başvuru formunda paylaştığım kimlik, iletişim ve yatırım bilgilerimin Soohouse Coffee® tarafından franchise değerlendirme ve iletişim süreçlerinin yürütülmesi amacıyla işlenmesini ve saklanmasını kabul ediyorum.', 'type' => 'textarea', 'label' => 'KVKK Aydınlatma Metni'],
            ['group' => 'franchise', 'key' => 'franchise_notify_email', 'value' => '', 'type' => 'text', 'label' => 'Franchise Başvuru Bildirim E-postası'],

            // İletişim & Sosyal Medya (Bilmediğimiz telefon/adres bilgisi uydurulmaz; panelden girilene kadar boş bırakılır)
            ['group' => 'contact', 'key' => 'hq_address', 'value' => '', 'type' => 'textarea', 'label' => 'Genel Merkez Adresi'],
            ['group' => 'contact', 'key' => 'hq_phone', 'value' => '', 'type' => 'text', 'label' => 'Genel Merkez Telefonu'],
            ['group' => 'contact', 'key' => 'hq_whatsapp', 'value' => '', 'type' => 'text', 'label' => 'Genel Merkez WhatsApp'],
            ['group' => 'contact', 'key' => 'hq_email', 'value' => '', 'type' => 'text', 'label' => 'Genel Merkez E-posta'],
            ['group' => 'contact', 'key' => 'hq_maps_embed', 'value' => '', 'type' => 'text', 'label' => 'Genel Merkez Harita Bağlantısı'],
            ['group' => 'social', 'key' => 'instagram_url', 'value' => '', 'type' => 'text', 'label' => 'Instagram URL'],
            ['group' => 'social', 'key' => 'facebook_url', 'value' => '', 'type' => 'text', 'label' => 'Facebook URL'],
            ['group' => 'social', 'key' => 'linkedin_url', 'value' => '', 'type' => 'text', 'label' => 'LinkedIn URL'],
            ['group' => 'social', 'key' => 'youtube_url', 'value' => '', 'type' => 'text', 'label' => 'YouTube URL'],
        ];

        foreach ($defaults as $item) {
            SiteSetting::firstOrCreate(
                ['key' => $item['key']],
                $item
            );
        }

        SiteSetting::clearCache();
    }

    protected function seedSeoPages(): void
    {
        $pages = [
            [
                'route_name' => 'home',
                'page_label' => 'Ana Sayfa (/)',
                'meta_title' => 'Soohouse Coffee® | Topraktan Fincana Nitelikli Kahve & Franchise',
                'meta_description' => 'Soohouse Coffee® resmi web sitesi. Nitelikli kahve çekirdeklerimiz, şubelerimiz, dijital QR menümüz ve franchise başvuru fırsatlarını keşfedin.',
            ],
            [
                'route_name' => 'about',
                'page_label' => 'Hakkımızda (/hakkimizda)',
                'meta_title' => 'Biz Kimiz | Soohouse Coffee® Marka Hikayesi',
                'meta_description' => 'Soohouse Coffee® olarak topraktan fincana uzanan nitelikli kahve felsefemizi, vizyonumuzu ve kalite standartlarımızı inceleyin.',
            ],
            [
                'route_name' => 'products',
                'page_label' => 'Ürünlerimiz (/urunlerimiz)',
                'meta_title' => 'Kahve Çekirdekleri ve Ürünlerimiz | Soohouse Coffee®',
                'meta_description' => 'Soohouse Coffee® espresso harmanları, nitelikli filtre kahveler, soğuk demlemeler ve imza lezzetlerimiz.',
            ],
            [
                'route_name' => 'branches',
                'page_label' => 'Şubelerimiz (/subelerimiz)',
                'meta_title' => 'Şubelerimiz ve Noktalarımız | Soohouse Coffee®',
                'meta_description' => 'Size en yakın Soohouse Coffee® şubesini harita üzerinde bulun, çalışma saatlerini ve şube QR menüsünü inceleyin.',
            ],
            [
                'route_name' => 'franchise',
                'page_label' => 'Franchise (/franchise)',
                'meta_title' => 'Franchise Başvurusu | Soohouse Coffee® Yatırımcı Ekosistemi',
                'meta_description' => 'Soohouse Coffee® franchise sistemi ile kendi kafenizi açın. Yatırım koşulları, operasyonel destek ve online franchise başvuru formu.',
            ],
            [
                'route_name' => 'press',
                'page_label' => 'Basında Biz (/basinda-biz)',
                'meta_title' => 'Basında Biz & Haberler | Soohouse Coffee®',
                'meta_description' => 'Soohouse Coffee® hakkında basında çıkan haberler, bültenler ve kurumsal duyurular.',
            ],
            [
                'route_name' => 'contact',
                'page_label' => 'İletişim (/iletisim)',
                'meta_title' => 'Genel Merkez İletişim | Soohouse Coffee®',
                'meta_description' => 'Soohouse Coffee® genel merkez iletişim bilgileri, adres ve kurumsal iletişim kanalları.',
            ],
        ];

        foreach ($pages as $page) {
            SeoPage::updateOrCreate(
                ['route_name' => $page['route_name']],
                $page
            );
        }

        SeoPage::clearCache();
    }

    protected function seedSuperAdmin(): void
    {
        $adminEmail = env('SUPER_ADMIN_EMAIL');
        $adminPassword = env('SUPER_ADMIN_PASSWORD');
        $adminName = env('SUPER_ADMIN_NAME', 'Soohouse Süper Admin');

        if (! empty($adminEmail) && ! empty($adminPassword)) {
            $user = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => $adminName,
                    'password' => Hash::make($adminPassword),
                    'is_active' => true,
                    'all_branches' => true,
                ]
            );

            $superRole = Role::where('slug', 'super_admin')->first();
            if ($superRole) {
                $user->roles()->syncWithoutDetaching([$superRole->id]);
            }
        }
    }
}
