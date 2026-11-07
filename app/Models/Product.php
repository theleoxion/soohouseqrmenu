<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'short_description',
        'description',
        'image',
        'base_price',
        'min_price',
        'max_price',
        'tags',
        'temperature_options',
        'variants',
        'bean_origin',
        'roast_level',
        'tasting_notes',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'float',
            'min_price' => 'float',
            'max_price' => 'float',
            'tags' => 'array',
            'temperature_options' => 'array',
            'variants' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_product')
            ->using(BranchProduct::class)
            ->withPivot([
                'id',
                'is_available',
                'is_sold_out',
                'custom_price',
                'discount_type',
                'discount_value',
                'discount_starts_at',
                'discount_ends_at',
                'sort_order',
            ])
            ->withTimestamps();
    }

    public function branchProducts(): HasMany
    {
        return $this->hasMany(BranchProduct::class);
    }

    /**
     * Available tag definitions with Turkish labels.
     *
     * @return array<string, string>
     */
    public static function availableTags(): array
    {
        return [
            'signature' => 'İmza Lezzet',
            'new' => 'Yeni',
            'vegetarian' => 'Vejetaryen',
            'vegan' => 'Vegan',
            'lactose_free' => 'Laktozsuz Seçenekli',
            'sugar_free' => 'Şekersiz',
            'gluten_free' => 'Glutensiz',
        ];
    }

    /**
     * Available temperature options with Turkish labels.
     *
     * @return array<string, string>
     */
    public static function availableTemperatures(): array
    {
        return [
            'hot' => 'Sıcak',
            'cold' => 'Soğuk / Buzlu',
        ];
    }
}
