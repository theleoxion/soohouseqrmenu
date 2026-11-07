<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'branch_id',
        'dining_table_id',
        'type',
        'message',
        'customer_name',
        'customer_contact',
        'status',
        'internal_note',
        'submitted_date',
        'submitted_time',
        'day_name',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'submitted_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    public static function typeLabels(): array
    {
        return [
            'request' => 'İstek',
            'suggestion' => 'Öneri',
            'complaint' => 'Şikayet',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'new' => 'Yeni',
            'in_review' => 'İnceleniyor',
            'resolved' => 'Çözüldü',
        ];
    }

    public function typeLabel(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
