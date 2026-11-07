<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PressItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'publisher',
        'published_at',
        'summary',
        'content',
        'external_url',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
