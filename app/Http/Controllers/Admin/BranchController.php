<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\TurkishSlug;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchHour;
use App\Models\BranchProduct;
use App\Models\BranchSpecialDay;
use App\Models\Product;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::withCount(['diningTables', 'feedbacks'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create(): View
    {
        return view('admin.branches.form', [
            'branch' => new Branch(['is_active' => true, 'sort_order' => 1]),
            'hours' => $this->defaultHoursCollection(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateBranch($request);

        $branch = DB::transaction(function () use ($validated, $request) {
            $slug = ! empty($validated['slug'])
                ? TurkishSlug::make($validated['slug'])
                : TurkishSlug::make($validated['name']);

            $slug = $this->ensureUniqueBranchSlug($slug);

            if ($request->hasFile('cover_image_file')) {
                $validated['cover_image'] = $this->storeImage($request->file('cover_image_file'), 'branches');
            }

            $branch = Branch::create(array_merge($validated, ['slug' => $slug]));

            // Save 7 days working hours
            $this->syncBranchHours($branch, $request->input('hours', []));

            // Auto-attach active central products to new branch menu by default
            $products = Product::where('is_active', true)->get();
            foreach ($products as $idx => $product) {
                BranchProduct::firstOrCreate([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                ], [
                    'is_available' => true,
                    'is_sold_out' => false,
                    'sort_order' => $idx + 1,
                ]);
            }

            AuditLogger::record(
                actionType: 'branch.created',
                description: "Yeni şube oluşturuldu: {$branch->name} (/subelerimiz/{$branch->slug} ve /qrmenu/{$branch->slug})",
                auditable: $branch,
                newValues: $branch->toArray(),
                branchId: $branch->id
            );

            return $branch;
        });

        return redirect()->route('admin.branches.index')
            ->with('status', "{$branch->name} şubesi başarıyla oluşturuldu. Şube detay (/subelerimiz/{$branch->slug}) ve QR menü (/qrmenu/{$branch->slug}) sayfaları yayına alındı.");
    }

    public function edit(Branch $branch): View
    {
        $branch->load(['hours', 'specialDays']);
        $hours = $branch->hours->count() === 7
            ? $branch->hours->keyBy('day_of_week')
            : $this->defaultHoursCollection($branch->hours->keyBy('day_of_week'));

        return view('admin.branches.form', compact('branch', 'hours'));
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $this->validateBranch($request, $branch->id);
        $oldValues = $branch->only(['name', 'slug', 'city', 'district', 'address', 'phone', 'is_active']);

        DB::transaction(function () use ($branch, $validated, $request, $oldValues) {
            $slug = ! empty($validated['slug'])
                ? TurkishSlug::make($validated['slug'])
                : TurkishSlug::make($validated['name']);

            $slug = $this->ensureUniqueBranchSlug($slug, $branch->id);

            if ($request->hasFile('cover_image_file')) {
                $validated['cover_image'] = $this->storeImage($request->file('cover_image_file'), 'branches');
            }

            $branch->update(array_merge($validated, ['slug' => $slug]));
            $this->syncBranchHours($branch, $request->input('hours', []));

            AuditLogger::record(
                actionType: 'branch.updated',
                description: "{$branch->name} şube bilgileri güncellendi.",
                auditable: $branch,
                oldValues: $oldValues,
                newValues: $branch->only(['name', 'slug', 'city', 'district', 'address', 'phone', 'is_active']),
                branchId: $branch->id
            );
        });

        return redirect()->route('admin.branches.index')
            ->with('status', "{$branch->name} şube bilgileri güncellendi.");
    }

    /**
     * Branch manager's own branch settings & working hours editor.
     */
    public function editOwnBranch(Branch $branch): View
    {
        $branch->load(['hours', 'specialDays']);
        $hours = $branch->hours->count() === 7
            ? $branch->hours->keyBy('day_of_week')
            : $this->defaultHoursCollection($branch->hours->keyBy('day_of_week'));

        return view('admin.branches.own-settings', compact('branch', 'hours'));
    }

    public function updateOwnBranch(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'string', 'max:500'],
            'hours' => ['nullable', 'array'],
        ]);

        $oldValues = $branch->only(['phone', 'whatsapp', 'email', 'address', 'latitude', 'longitude']);

        DB::transaction(function () use ($branch, $validated, $request, $oldValues) {
            $branch->update(collect($validated)->except('hours')->all());
            $this->syncBranchHours($branch, $request->input('hours', []));

            AuditLogger::record(
                actionType: 'branch.updated',
                description: "{$branch->name} şube iletişim ve çalışma saatleri güncellendi.",
                auditable: $branch,
                oldValues: $oldValues,
                newValues: $branch->only(['phone', 'whatsapp', 'email', 'address', 'latitude', 'longitude']),
                branchId: $branch->id
            );
        });

        return back()->with('status', 'Şube bilgileri ve çalışma saatleri başarıyla güncellendi.');
    }

    public function storeSpecialDay(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'is_closed' => ['nullable', 'boolean'],
            'open_time' => ['nullable', 'string'],
            'close_time' => ['nullable', 'string'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        BranchSpecialDay::updateOrCreate(
            ['branch_id' => $branch->id, 'date' => $validated['date']],
            [
                'is_closed' => $request->boolean('is_closed', true),
                'open_time' => $validated['open_time'] ?: null,
                'close_time' => $validated['close_time'] ?: null,
                'note' => $validated['note'] ?? null,
            ]
        );

        AuditLogger::record(
            actionType: 'branch.updated',
            description: "{$branch->name} için özel gün/tatil saati tanımlandı ({$validated['date']}).",
            auditable: $branch,
            newValues: $validated,
            branchId: $branch->id
        );

        return back()->with('status', 'Özel gün / tatil çalışma durumu kaydedildi.');
    }

    public function destroySpecialDay(Branch $branch, BranchSpecialDay $specialDay): RedirectResponse
    {
        if ($specialDay->branch_id !== $branch->id) {
            abort(403);
        }

        $specialDay->delete();

        return back()->with('status', 'Özel gün kaydı silindi.');
    }

    protected function validateBranch(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'instagram_url' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'string', 'max:255'],
            'google_maps_url' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:3000'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'cover_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        unset($data['cover_image_file']);

        return $data;
    }

    protected function syncBranchHours(Branch $branch, array $hoursInput): void
    {
        for ($day = 1; $day <= 7; $day++) {
            $dayData = $hoursInput[$day] ?? [];
            BranchHour::updateOrCreate(
                ['branch_id' => $branch->id, 'day_of_week' => $day],
                [
                    'open_time' => ! empty($dayData['open_time']) ? $dayData['open_time'] : '08:00',
                    'close_time' => ! empty($dayData['close_time']) ? $dayData['close_time'] : '23:00',
                    'is_closed' => ! empty($dayData['is_closed']),
                ]
            );
        }
    }

    protected function defaultHoursCollection($existing = null)
    {
        $collection = collect();
        for ($day = 1; $day <= 7; $day++) {
            if ($existing && $existing->has($day)) {
                $collection->put($day, $existing->get($day));
            } else {
                $collection->put($day, new BranchHour([
                    'day_of_week' => $day,
                    'open_time' => '08:00',
                    'close_time' => '23:00',
                    'is_closed' => false,
                ]));
            }
        }

        return $collection;
    }

    protected function ensureUniqueBranchSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug ?: 'sube';
        $counter = 2;

        while (Branch::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function storeImage($file, string $folder): string
    {
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $destDir = public_path("uploads/{$folder}");
        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $file->move($destDir, $filename);

        return "/uploads/{$folder}/{$filename}";
    }
}
