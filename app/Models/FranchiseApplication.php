<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FranchiseApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'city',
        'district',
        'investment_budget',
        'preferred_location_note',
        'message',
        'kvkk_accepted',
        'status',
        'internal_notes',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'kvkk_accepted' => 'boolean',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'new' => 'Yeni',
            'called' => 'Arandı',
            'interviewed' => 'Görüşüldü',
            'positive' => 'Olumlu',
            'negative' => 'Olumsuz',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }
}
