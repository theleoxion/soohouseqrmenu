<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogger;
use App\Services\PriceCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class BranchMenuController extends Controller
{
    public function __construct(protected PriceCalculationService $priceService) {}

    public function index(Request $request, Branch $branch): View
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $pivots = BranchProduct::where('branch_id', $branch->id)->get()->keyBy('product_id');

        $productsQuery = Product::with('category')->where('is_active', true)->orderBy('category_id')->orderBy('sort_order');

        if ($request->filled('category_id')) {
            $productsQuery->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $productsQuery->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $products = $productsQuery->get()->map(function (Product $product) use ($pivots) {
            /** @var BranchProduct|null $pivot */
            $pivot = $pivots->get($product->id);
            $product->setAttribute('pivot_record', $pivot);
            $product->setAttribute('calculated_price', $this->priceService->calculate($product, $pivot));

            return $product;
        });

        return view('admin.branch-menu.index', compact('branch', 'categories', 'products'));
    }

    /**
     * Update a single product's availability, sold-out state, custom branch price, and discount for this branch.
     */
    public function updateItem(Request $request, Branch $branch, Product $product): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'is_available' => ['nullable', 'boolean'],
            'is_sold_out' => ['nullable', 'boolean'],
            'custom_price' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'string', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_starts_at' => ['nullable', 'date'],
            'discount_ends_at' => ['nullable', 'date', 'after_or_equal:discount_starts_at'],
        ]);

        $customPrice = isset($validated['custom_price']) && $validated['custom_price'] !== ''
            ? round((float) $validated['custom_price'], 2)
            : null;

        // Enforce central min/max limits on branch custom price
        try {
            $this->priceService->validateBranchPriceLimits($product, $customPrice);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['custom_price' => $e->getMessage()]);
        }

        $pivot = BranchProduct::firstOrNew([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
        ]);

        $oldValues = $pivot->exists
            ? $pivot->only(['is_available', 'is_sold_out', 'custom_price', 'discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at'])
            : [];

        if ($user->hasPermission('branch_menu.manage')) {
            $pivot->is_available = $request->boolean('is_available');
            $pivot->is_sold_out = $request->boolean('is_sold_out');
        }

        if ($user->hasPermission('prices.branch_update') || $user->hasPermission('prices.bulk_update')) {
            $pivot->custom_price = $customPrice;
            $pivot->discount_type = ! empty($validated['discount_type']) && ! empty($validated['discount_value'])
                ? $validated['discount_type']
                : null;
            $pivot->discount_value = ! empty($validated['discount_type']) && ! empty($validated['discount_value'])
                ? round((float) $validated['discount_value'], 2)
                : null;
            $pivot->discount_starts_at = ! empty($validated['discount_starts_at']) ? $validated['discount_starts_at'] : null;
            $pivot->discount_ends_at = ! empty($validated['discount_ends_at']) ? $validated['discount_ends_at'] : null;
        }

        $pivot->save();

        $newValues = $pivot->only(['is_available', 'is_sold_out', 'custom_price', 'discount_type', 'discount_value', 'discount_starts_at', 'discount_ends_at']);

        AuditLogger::record(
            actionType: 'price.branch_updated',
            description: "{$branch->name} şubesinde {$product->name} menü/fiyat durumu güncellendi.",
            auditable: $product,
            oldValues: $oldValues,
            newValues: $newValues,
            branchId: $branch->id
        );

        return back()->with('status', "{$product->name} şube menü ve fiyat ayarları kaydedildi.");
    }
}
