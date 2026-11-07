<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\TurkishSlug;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('sort_order')->get();
        $query = Product::with('category')->orderBy('category_id')->orderBy('sort_order');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        $products = $query->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('sort_order')->get();
        $product = new Product([
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
            'temperature_options' => ['hot'],
            'tags' => [],
        ]);

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        if ($request->hasFile('image_file')) {
            $validated['image'] = $this->storeUploadedImage($request->file('image_file'));
        }

        $validated['slug'] = $this->uniqueSlug($validated['name']);
        $product = Product::create($validated);

        // Attach to all branches if requested
        if ($request->boolean('attach_to_all_branches', true)) {
            foreach (Branch::all() as $branch) {
                BranchProduct::firstOrCreate([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                ], [
                    'is_available' => true,
                    'is_sold_out' => false,
                ]);
            }
        }

        AuditLogger::record(
            actionType: 'product.created',
            description: "Merkez ürün havuzuna yeni ürün eklendi: {$product->name} ({$product->sku}) - {$product->base_price} ₺",
            auditable: $product,
            newValues: $product->toArray()
        );

        return redirect()->route('admin.products.index')
            ->with('status', "{$product->name} merkez ürün havuzuna başarıyla eklendi.");
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product->id);
        $oldValues = $product->only(['name', 'sku', 'category_id', 'base_price', 'min_price', 'max_price', 'is_active']);

        if ($request->hasFile('image_file')) {
            $validated['image'] = $this->storeUploadedImage($request->file('image_file'));
        }

        $product->update($validated);

        AuditLogger::record(
            actionType: 'product.updated',
            description: "Merkez ürünü güncellendi: {$product->name} ({$product->sku})",
            auditable: $product,
            oldValues: $oldValues,
            newValues: $product->only(['name', 'sku', 'category_id', 'base_price', 'min_price', 'max_price', 'is_active'])
        );

        return redirect()->route('admin.products.index')
            ->with('status', "{$product->name} bilgileri güncellendi.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $oldValues = $product->toArray();
        $name = $product->name;
        $product->delete();

        AuditLogger::record(
            actionType: 'product.deleted',
            description: "Ürün havuzundan silindi: {$name}",
            oldValues: $oldValues
        );

        return redirect()->route('admin.products.index')
            ->with('status', "{$name} ürün havuzundan silindi.");
    }

    protected function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:150'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:3000'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string'],
            'temperature_options' => ['nullable', 'array'],
            'temperature_options.*' => ['string', 'in:hot,cold'],
            'variants_json' => ['nullable', 'string'],
            'bean_origin' => ['nullable', 'string', 'max:150'],
            'roast_level' => ['nullable', 'string', 'max:150'],
            'tasting_notes' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if (! empty($validated['min_price']) && ! empty($validated['max_price']) && (float) $validated['min_price'] > (float) $validated['max_price']) {
            throw ValidationException::withMessages([
                'min_price' => 'Alt fiyat sınırı üst fiyat sınırından yüksek olamaz.',
            ]);
        }

        $variants = [];
        if (! empty($validated['variants_json'])) {
            $decoded = json_decode($validated['variants_json'], true);
            if (is_array($decoded)) {
                $variants = $decoded;
            }
        }

        return [
            'category_id' => (int) $validated['category_id'],
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => $validated['name'],
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'] ?? null,
            'base_price' => (float) $validated['base_price'],
            'min_price' => isset($validated['min_price']) && $validated['min_price'] !== '' ? (float) $validated['min_price'] : null,
            'max_price' => isset($validated['max_price']) && $validated['max_price'] !== '' ? (float) $validated['max_price'] : null,
            'tags' => $validated['tags'] ?? [],
            'temperature_options' => $validated['temperature_options'] ?? ['hot'],
            'variants' => $variants,
            'bean_origin' => $validated['bean_origin'] ?? null,
            'roast_level' => $validated['roast_level'] ?? null,
            'tasting_notes' => $validated['tasting_notes'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = TurkishSlug::make($name) ?: 'urun';
        $slug = $base;
        $i = 2;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    protected function storeUploadedImage($file): string
    {
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $dest = public_path('uploads/products');
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $file->move($dest, $filename);

        return "/uploads/products/{$filename}";
    }
}
