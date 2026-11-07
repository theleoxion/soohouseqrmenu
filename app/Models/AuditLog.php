<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'user_name',
        'branch_id',
        'action_type',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Denetim kayıtları (AuditLog) değiştirilemez.');
        });

        static::deleting(function () {
            throw new LogicException('Denetim kayıtları (AuditLog) silinemez.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public static function actionTypeLabels(): array
    {
        return [
            'auth.login' => 'Sisteme Giriş',
            'auth.logout' => 'Sistemden Çıkış',
            'price.branch_updated' => 'Şube Fiyat/İndirim Güncelleme',
            'price.bulk_updated' => 'Merkez Toplu Fiyat Güncelleme',
            'branch_menu.updated' => 'Şube Menü / Tükendi Güncelleme',
            'branch.created' => 'Yeni Şube Oluşturma',
            'branch.updated' => 'Şube Bilgisi / Saat Güncelleme',
            'product.created' => 'Ürün Havuzuna Ekleme',
            'product.updated' => 'Ürün Havuzu Güncelleme',
            'product.deleted' => 'Ürün Havuzundan Silme',
            'product.excel_imported' => 'Excel ile Toplu Ürün Aktarımı',
            'category.created' => 'Kategori Oluşturma',
            'category.updated' => 'Kategori Güncelleme / Sıralama',
            'category.deleted' => 'Kategori Silme',
            'table.created' => 'Masa Ekleme',
            'table.updated' => 'Masa Güncelleme',
            'table.deleted' => 'Masa Silme',
            'user.created' => 'Kullanıcı Oluşturma',
            'user.updated' => 'Kullanıcı / Yetki Güncelleme',
            'role.created' => 'Rol Oluşturma',
            'role.updated' => 'Rol İzinleri Güncelleme',
            'settings.updated' => 'Site / CMS / SEO Ayarları Güncelleme',
            'franchise.updated' => 'Franchise Başvuru Durumu Güncelleme',
            'feedback.updated' => 'Müşteri Geri Bildirimi Güncelleme',
        ];
    }

    public function actionLabel(): string
    {
        return self::actionTypeLabels()[$this->action_type] ?? $this->action_type;
    }
}
