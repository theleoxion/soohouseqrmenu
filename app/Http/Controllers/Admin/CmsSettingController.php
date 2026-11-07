<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\TurkishSlug;
use App\Http\Controllers\Controller;
use App\Models\PressItem;
use App\Models\SeoPage;
use App\Models\SiteSetting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CmsSettingController extends Controller
{
    public function index(): View
    {
        $settings = SiteSetting::orderBy('group')->orderBy('id')->get()->groupBy('group');
        $seoPages = SeoPage::orderBy('id')->get();
        $pressItems = PressItem::orderBy('sort_order')->orderByDesc('published_at')->get();

        return view('admin.settings.index', compact('settings', 'seoPages', 'pressItems'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $inputs = $request->input('settings', []);

        foreach ($inputs as $key => $value) {
            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        foreach (['logo_light', 'logo_icon', 'favicon'] as $fileKey) {
            if ($request->hasFile("files.{$fileKey}")) {
                $path = $this->storeBrandUpload($request->file("files.{$fileKey}"));
                SiteSetting::where('key', $fileKey)->update(['value' => $path]);
            }
        }

        SiteSetting::clearCache();

        AuditLogger::record(
            actionType: 'settings.updated',
            description: 'Site genel ayarları, tema renkleri ve kurumsal CMS metinleri güncellendi.',
            newValues: array_keys($inputs)
        );

        return back()->with('status', 'Site ayarları ve kurumsal içerikler başarıyla güncellendi.');
    }

    public function updateSeo(Request $request, SeoPage $seoPage): RedirectResponse
    {
        $validated = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'string', 'max:255'],
            'og_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('og_image_file')) {
            $seoPage->og_image = $this->storeBrandUpload($request->file('og_image_file'));
        }

        $seoPage->meta_title = $validated['meta_title'] ?? null;
        $seoPage->meta_description = $validated['meta_description'] ?? null;
        $seoPage->og_title = $validated['og_title'] ?? null;
        $seoPage->og_description = $validated['og_description'] ?? null;
        $seoPage->canonical_url = $validated['canonical_url'] ?? null;
        $seoPage->noindex = $request->boolean('noindex');
        $seoPage->save();

        SeoPage::clearCache();

        AuditLogger::record(
            actionType: 'settings.updated',
            description: "{$seoPage->page_label} sayfası SEO ayarları güncellendi.",
            auditable: $seoPage
        );

        return back()->with('status', "{$seoPage->page_label} SEO meta verileri güncellendi.");
    }

    public function storePressItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'published_at' => ['nullable', 'date'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:5000'],
            'external_url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $slugBase = TurkishSlug::make($validated['title']) ?: 'basin';
        $slug = $slugBase.'-'.Str::lower(Str::random(4));

        $imagePath = $request->hasFile('image_file')
            ? $this->storeBrandUpload($request->file('image_file'))
            : null;

        $press = PressItem::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'publisher' => $validated['publisher'] ?? null,
            'published_at' => $validated['published_at'] ?? now()->toDateString(),
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'] ?? null,
            'external_url' => $validated['external_url'] ?? null,
            'image' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        AuditLogger::record(
            actionType: 'settings.updated',
            description: "Basında Biz bölümüne yeni içerik eklendi: {$press->title}",
            auditable: $press
        );

        return back()->with('status', 'Basın haberi başarıyla eklendi.');
    }

    public function destroyPressItem(PressItem $pressItem): RedirectResponse
    {
        $title = $pressItem->title;
        $pressItem->delete();

        AuditLogger::record(
            actionType: 'settings.updated',
            description: "Basında Biz içeriği silindi: {$title}"
        );

        return back()->with('status', 'Basın haberi silindi.');
    }

    protected function storeBrandUpload($file): string
    {
        $filename = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
        $dest = public_path('uploads/cms');
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        $file->move($dest, $filename);

        return "/uploads/cms/{$filename}";
    }
}
