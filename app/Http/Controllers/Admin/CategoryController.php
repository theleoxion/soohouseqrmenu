<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\TurkishSlug;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $slug = $this->uniqueSlug($validated['name']);
        $imagePath = $request->hasFile('image_file')
            ? $this->storeUploadedImage($request->file('image_file'))
            : null;

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'sort_order' => (int) ($validated['sort_order'] ?? (Category::max('sort_order') + 1)),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::record(
            actionType: 'category.created',
            description: "Yeni kategori eklendi: {$category->name}",
            auditable: $category,
            newValues: $category->toArray()
        );

        return back()->with('status', "{$category->name} kategorisi eklendi.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $oldValues = $category->only(['name', 'sort_order', 'is_active']);

        if ($request->hasFile('image_file')) {
            $category->image = $this->storeUploadedImage($request->file('image_file'));
        }

        $category->name = $validated['name'];
        $category->description = $validated['description'] ?? null;
        $category->sort_order = (int) ($validated['sort_order'] ?? $category->sort_order);
        $category->is_active = $request->boolean('is_active');
        $category->save();

        AuditLogger::record(
            actionType: 'category.updated',
            description: "{$category->name} kategorisi güncellendi.",
            auditable: $category,
            oldValues: $oldValues,
            newValues: $category->only(['name', 'sort_order', 'is_active'])
        );

        return back()->with('status', "{$category->name} kategorisi güncellendi.");
    }

    public function reorder(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'ordered_ids' => ['required', 'array'],
            'ordered_ids.*' => ['integer', 'exists:categories,id'],
        ]);

        foreach ($validated['ordered_ids'] as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        AuditLogger::record(
            actionType: 'category.updated',
            description: 'Kategori sıralaması (sürükle-bırak) güncellendi.',
            newValues: ['ordered_ids' => $validated['ordered_ids']]
        );

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'message' => 'Sıralama kaydedildi.']);
        }

        return back()->with('status', 'Kategori sıralaması kaydedildi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors([
                'name' => 'Bu kategoriye bağlı ürünler bulunduğu için silinemez. Önce ürünleri taşıyın veya pasife alın.',
            ]);
        }

        $name = $category->name;
        $category->delete();

        AuditLogger::record(
            actionType: 'category.deleted',
            description: "{$name} kategorisi silindi."
        );

        return back()->with('status', "{$name} kategorisi silindi.");
    }

    protected function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = TurkishSlug::make($name) ?: 'kategori';
        $slug = $base;
        $i = 2;
        while (Category::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    protected function storeUploadedImage($file): string
    {
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $dest = public_path('uploads/categories');
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $file->move($dest, $filename);

        return "/uploads/categories/{$filename}";
    }
}
