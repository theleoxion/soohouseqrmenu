<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BranchProduct extends Pivot
{
    protected $table = 'branch_product';

    public $incrementing = true;

    protected $fillable = [
        'branch_id',
        'product_id',
        'is_available',
        'is_sold_out',
        'custom_price',
        'discount_type',
        'discount_value',
        'discount_starts_at',
        'discount_ends_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'is_sold_out' => 'boolean',
            'custom_price' => 'float',
            'discount_value' => 'float',
            'discount_starts_at' => 'datetime',
            'discount_ends_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
