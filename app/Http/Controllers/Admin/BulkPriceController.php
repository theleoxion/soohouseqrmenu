<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Services\PriceCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BulkPriceController extends Controller
{
    public function __construct(protected PriceCalculationService $priceService) {}

    public function index(): View
    {
        $branches = Branch::orderBy('name')->get();
        $categories = Category::orderBy('sort_order')->get();
        $products = Product::with('category')->orderBy('category_id')->orderBy('sort_order')->get();

        return view('admin.pricing.index', [
            'branches' => $branches,
            'categories' => $categories,
            'products' => $products,
            'preview' => null,
            'filters' => [],
        ]);
    }

    public function preview(Request $request): View
    {
        $validated = $this->validateBulkRequest($request);

        $preview = $this->priceService->previewBulkUpdate(
            targetScope: $validated['target_scope'],
            operation: $validated['operation'],
            amount: (float) $validated['amount'],
            overwriteBranchCustomPrices: $request->boolean('overwrite_branch_custom'),
            branchIds: $validated['branch_ids'] ?? [],
            categoryIds: $validated['category_ids'] ?? [],
            productIds: $validated['product_ids'] ?? []
        );

        $branches = Branch::orderBy('name')->get();
        $categories = Category::orderBy('sort_order')->get();
        $products = Product::with('category')->orderBy('category_id')->orderBy('sort_order')->get();

        return view('admin.pricing.index', [
            'branches' => $branches,
            'categories' => $categories,
            'products' => $products,
            'preview' => $preview,
            'filters' => array_merge($validated, [
                'overwrite_branch_custom' => $request->boolean('overwrite_branch_custom'),
            ]),
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        $validated = $this->validateBulkRequest($request);

        $affected = $this->priceService->applyBulkUpdate(
            targetScope: $validated['target_scope'],
            operation: $validated['operation'],
            amount: (float) $validated['amount'],
            overwriteBranchCustomPrices: $request->boolean('overwrite_branch_custom'),
            branchIds: $validated['branch_ids'] ?? [],
            categoryIds: $validated['category_ids'] ?? [],
            productIds: $validated['product_ids'] ?? []
        );

        return redirect()->route('admin.pricing.index')
            ->with('status', "Toplu fiyat güncellemesi başarıyla uygulandı ve denetim kaydına (Audit Log) işlendi ({$affected} kayıt güncellendi).");
    }

    protected function validateBulkRequest(Request $request): array
    {
        return $request->validate([
            'target_scope' => ['required', 'string', 'in:central_and_branches,selected_branches'],
            'operation' => ['required', 'string', 'in:percentage_increase,percentage_decrease,fixed_add,fixed_subtract,exact_price'],
            'amount' => ['required', 'numeric', 'min:0'],
            'overwrite_branch_custom' => ['nullable', 'boolean'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['integer', 'exists:branches,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);
    }
}
