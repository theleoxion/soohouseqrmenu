<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PriceCalculationService
{
    /**
     * Calculate the effective customer price according to the hierarchy:
     * Central Base Price -> Branch Custom Price (override) -> Branch Active Discount.
     *
     * @return array{
     *   base_price: float,
     *   branch_price: float,
     *   final_price: float,
     *   original_display_price: float,
     *   has_branch_override: bool,
     *   has_discount: bool,
     *   discount_amount: float,
     *   discount_label: string|null,
     *   formatted_final: string,
     *   formatted_original: string
     * }
     */
    public function calculate(Product $product, ?BranchProduct $pivot = null, ?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now('Europe/Istanbul');
        $basePrice = round((float) $product->base_price, 2);

        $hasBranchOverride = $pivot !== null && $pivot->custom_price !== null && (float) $pivot->custom_price > 0;
        $branchPrice = $hasBranchOverride ? round((float) $pivot->custom_price, 2) : $basePrice;

        $hasDiscount = false;
        $discountAmount = 0.0;
        $discountLabel = null;
        $finalPrice = $branchPrice;

        if ($pivot && $this->isDiscountActive($pivot, $now)) {
            $discountValue = (float) $pivot->discount_value;

            if ($pivot->discount_type === 'percentage' && $discountValue > 0 && $discountValue <= 100) {
                $discountAmount = round($branchPrice * ($discountValue / 100), 2);
                $finalPrice = max(0.0, round($branchPrice - $discountAmount, 2));
                $hasDiscount = $discountAmount > 0;
                $discountLabel = '%'.rtrim(rtrim(number_format($discountValue, 2, ',', '.'), '0'), ',').' İndirim';
            } elseif ($pivot->discount_type === 'fixed' && $discountValue > 0) {
                $discountAmount = min($branchPrice, round($discountValue, 2));
                $finalPrice = max(0.0, round($branchPrice - $discountAmount, 2));
                $hasDiscount = $discountAmount > 0;
                $discountLabel = self::formatCurrency($discountValue).' İndirim';
            }
        }

        return [
            'base_price' => $basePrice,
            'branch_price' => $branchPrice,
            'final_price' => $finalPrice,
            'original_display_price' => $branchPrice,
            'has_branch_override' => $hasBranchOverride,
            'has_discount' => $hasDiscount,
            'discount_amount' => $discountAmount,
            'discount_label' => $discountLabel,
            'formatted_final' => self::formatCurrency($finalPrice),
            'formatted_original' => self::formatCurrency($branchPrice),
        ];
    }

    /**
     * Check whether the branch discount is currently active within optional start/end bounds.
     */
    public function isDiscountActive(BranchProduct $pivot, ?Carbon $now = null): bool
    {
        if (empty($pivot->discount_type) || empty($pivot->discount_value) || (float) $pivot->discount_value <= 0) {
            return false;
        }

        $now = $now ?: Carbon::now('Europe/Istanbul');

        if ($pivot->discount_starts_at && $now->lt($pivot->discount_starts_at)) {
            return false;
        }

        if ($pivot->discount_ends_at && $now->gt($pivot->discount_ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Validate that a branch custom price respects central min/max limits on the product.
     */
    public function validateBranchPriceLimits(Product $product, ?float $customPrice): void
    {
        if ($customPrice === null) {
            return;
        }

        if ($product->min_price !== null && $customPrice < (float) $product->min_price) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" ürünü için şube fiyatı merkez alt sınırından (%s) düşük olamaz.',
                    $product->name,
                    self::formatCurrency((float) $product->min_price)
                )
            );
        }

        if ($product->max_price !== null && $customPrice > (float) $product->max_price) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" ürünü için şube fiyatı merkez üst sınırından (%s) yüksek olamaz.',
                    $product->name,
                    self::formatCurrency((float) $product->max_price)
                )
            );
        }
    }

    /**
     * Compute a new price given an operation type ('percentage_increase', 'percentage_decrease', 'fixed_add', 'fixed_subtract', 'exact_price').
     */
    public function computeUpdatedPrice(float $currentPrice, string $operation, float $amount): float
    {
        $newPrice = match ($operation) {
            'percentage_increase' => $currentPrice * (1 + ($amount / 100)),
            'percentage_decrease' => $currentPrice * (1 - ($amount / 100)),
            'fixed_add' => $currentPrice + $amount,
            'fixed_subtract' => $currentPrice - $amount,
            'exact_price' => $amount,
            default => throw new InvalidArgumentException('Geçersiz fiyat güncelleme işlemi.'),
        };

        return max(0.0, round($newPrice, 2));
    }

    /**
     * Generate a preview of bulk price changes before applying them.
     *
     * @param  array<int>  $branchIds  Empty means central base price + optionally all branches
     * @param  array<int>  $categoryIds
     * @param  array<int>  $productIds
     * @return Collection<int, array<string, mixed>>
     */
    public function previewBulkUpdate(
        string $targetScope, // 'central_and_branches' | 'selected_branches'
        string $operation,
        float $amount,
        bool $overwriteBranchCustomPrices = false,
        array $branchIds = [],
        array $categoryIds = [],
        array $productIds = []
    ): Collection {
        $productsQuery = Product::query()->with('category')->orderBy('category_id')->orderBy('sort_order');

        if (! empty($categoryIds)) {
            $productsQuery->whereIn('category_id', $categoryIds);
        }

        if (! empty($productIds)) {
            $productsQuery->whereIn('id', $productIds);
        }

        $products = $productsQuery->get();
        $branches = $targetScope === 'selected_branches' && ! empty($branchIds)
            ? Branch::whereIn('id', $branchIds)->get()
            : Branch::all();

        $previewRows = collect();

        foreach ($products as $product) {
            $oldBase = (float) $product->base_price;
            $newBase = $targetScope === 'central_and_branches'
                ? $this->computeUpdatedPrice($oldBase, $operation, $amount)
                : $oldBase;

            $branchDiffs = [];

            foreach ($branches as $branch) {
                /** @var BranchProduct|null $pivot */
                $pivot = BranchProduct::where('branch_id', $branch->id)
                    ->where('product_id', $product->id)
                    ->first();

                $hadCustomPrice = $pivot && $pivot->custom_price !== null;
                $oldEffectiveBranchPrice = $hadCustomPrice ? (float) $pivot->custom_price : $oldBase;

                if ($targetScope === 'central_and_branches') {
                    if ($hadCustomPrice && ! $overwriteBranchCustomPrices) {
                        // Keep custom branch price untouched
                        $newEffectiveBranchPrice = $oldEffectiveBranchPrice;
                        $actionNote = 'Özel şube fiyatı korundu';
                    } else {
                        $newEffectiveBranchPrice = $newBase;
                        $actionNote = $hadCustomPrice ? 'Özel şube fiyatı merkez fiyatıyla ezildi' : 'Merkez fiyatı güncellendi';
                    }
                } else {
                    // Selected branches update
                    if ($hadCustomPrice && ! $overwriteBranchCustomPrices) {
                        $newEffectiveBranchPrice = $oldEffectiveBranchPrice;
                        $actionNote = 'Özel şube fiyatı korundu (atlandı)';
                    } else {
                        $newEffectiveBranchPrice = $this->computeUpdatedPrice($oldEffectiveBranchPrice, $operation, $amount);
                        $actionNote = 'Şube fiyatı güncellendi';
                    }
                }

                $branchDiffs[] = [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'had_custom_price' => $hadCustomPrice,
                    'old_price' => $oldEffectiveBranchPrice,
                    'new_price' => $newEffectiveBranchPrice,
                    'action_note' => $actionNote,
                ];
            }

            $previewRows->push([
                'product_id' => $product->id,
                'sku' => $product->sku,
                'product_name' => $product->name,
                'category_name' => $product->category?->name ?? '-',
                'old_base_price' => $oldBase,
                'new_base_price' => $newBase,
                'branches' => $branchDiffs,
            ]);
        }

        return $previewRows;
    }

    /**
     * Execute and log a bulk price update transactionally.
     *
     * @param  array<int>  $branchIds
     * @param  array<int>  $categoryIds
     * @param  array<int>  $productIds
     */
    public function applyBulkUpdate(
        string $targetScope,
        string $operation,
        float $amount,
        bool $overwriteBranchCustomPrices = false,
        array $branchIds = [],
        array $categoryIds = [],
        array $productIds = []
    ): int {
        $preview = $this->previewBulkUpdate(
            $targetScope,
            $operation,
            $amount,
            $overwriteBranchCustomPrices,
            $branchIds,
            $categoryIds,
            $productIds
        );

        $affectedCount = 0;

        DB::transaction(function () use ($preview, $targetScope, $overwriteBranchCustomPrices, $operation, $amount, &$affectedCount) {
            foreach ($preview as $row) {
                $product = Product::find($row['product_id']);
                if (! $product) {
                    continue;
                }

                if ($targetScope === 'central_and_branches') {
                    $oldBase = (float) $product->base_price;
                    $newBase = (float) $row['new_base_price'];

                    if ($oldBase !== $newBase) {
                        $product->update(['base_price' => $newBase]);
                    }

                    if ($overwriteBranchCustomPrices) {
                        BranchProduct::where('product_id', $product->id)
                            ->whereNotNull('custom_price')
                            ->update(['custom_price' => null]);
                    }

                    AuditLogger::record(
                        actionType: 'price.bulk_updated',
                        description: "{$product->name} merkez fiyatı toplu güncellendi ({$oldBase} ₺ -> {$newBase} ₺)",
                        auditable: $product,
                        oldValues: ['base_price' => $oldBase],
                        newValues: [
                            'base_price' => $newBase,
                            'overwrite_branch_custom' => $overwriteBranchCustomPrices,
                            'operation' => $operation,
                            'amount' => $amount,
                        ]
                    );

                    $affectedCount++;
                } else {
                    foreach ($row['branches'] as $branchDiff) {
                        if ($branchDiff['had_custom_price'] && ! $overwriteBranchCustomPrices) {
                            continue;
                        }

                        $pivot = BranchProduct::firstOrCreate(
                            [
                                'branch_id' => $branchDiff['branch_id'],
                                'product_id' => $product->id,
                            ],
                            [
                                'is_available' => true,
                                'is_sold_out' => false,
                            ]
                        );

                        $oldPrice = $branchDiff['old_price'];
                        $newPrice = $branchDiff['new_price'];

                        $pivot->update(['custom_price' => $newPrice]);

                        AuditLogger::record(
                            actionType: 'price.bulk_updated',
                            description: "{$branchDiff['branch_name']} - {$product->name} şube fiyatı toplu güncellendi ({$oldPrice} ₺ -> {$newPrice} ₺)",
                            auditable: $product,
                            oldValues: ['custom_price' => $oldPrice],
                            newValues: ['custom_price' => $newPrice, 'operation' => $operation, 'amount' => $amount],
                            branchId: $branchDiff['branch_id']
                        );

                        $affectedCount++;
                    }
                }
            }
        });

        return $affectedCount;
    }

    public static function formatCurrency(float $price): string
    {
        $formatted = number_format($price, 2, ',', '.');
        if (str_ends_with($formatted, ',00')) {
            $formatted = substr($formatted, 0, -3);
        }

        return $formatted.' ₺';
    }
}
