<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\FranchiseApplication;
use App\Models\PressItem;
use App\Models\Product;
use App\Models\SeoPage;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $seo = SeoPage::forRoute('home');
        $featuredProducts = Product::with('category')
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $branches = Branch::with(['hours', 'specialDays'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $pressItems = PressItem::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('public.home', compact('seo', 'featuredProducts', 'branches', 'pressItems'));
    }

    public function about(): View
    {
        $seo = SeoPage::forRoute('about');
        $branchCount = Branch::where('is_active', true)->count();
        $productCount = Product::where('is_active', true)->count();

        return view('public.about', compact('seo', 'branchCount', 'productCount'));
    }

    public function products(Request $request): View
    {
        $seo = SeoPage::forRoute('products');
        $categories = Category::where('is_active', true)
            ->with(['products' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return view('public.products', compact('seo', 'categories'));
    }

    public function branches(Request $request): View
    {
        $seo = SeoPage::forRoute('branches');
        $cityFilter = $request->query('sehir');

        $branchesQuery = Branch::with(['hours', 'specialDays'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($cityFilter) {
            $branchesQuery->where('city', $cityFilter);
        }

        $branches = $branchesQuery->get();
        $cities = Branch::where('is_active', true)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('public.branches', compact('seo', 'branches', 'cities', 'cityFilter'));
    }

    public function branchShow(Branch $branch): View
    {
        if (! $branch->is_active) {
            abort(404);
        }

        $branch->load(['hours', 'specialDays']);
        $openStatus = $branch->openStatus();

        return view('public.branch-show', compact('branch', 'openStatus'));
    }

    public function franchise(): View
    {
        $seo = SeoPage::forRoute('franchise');

        return view('public.franchise', compact('seo'));
    }

    public function storeFranchise(Request $request): RedirectResponse
    {
        // Honeypot spam check
        if ($request->filled('website_url')) {
            return back()->with('franchise_success', 'Başvurunuz alınmıştır.');
        }

        $throttleKey = 'franchise-submit|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return back()->withErrors([
                'full_name' => 'Kısa süre içinde çok fazla başvuru gönderdiniz. Lütfen daha sonra tekrar deneyin.',
            ])->withInput();
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'investment_budget' => ['required', 'string', 'max:150'],
            'preferred_location_note' => ['nullable', 'string', 'max:500'],
            'message' => ['nullable', 'string', 'max:2000'],
            'kvkk_accepted' => ['required', 'accepted'],
        ]);

        RateLimiter::hit($throttleKey, 300);

        $application = FranchiseApplication::create([
            'full_name' => strip_tags($validated['full_name']),
            'phone' => strip_tags($validated['phone']),
            'email' => strip_tags($validated['email']),
            'city' => strip_tags($validated['city']),
            'district' => isset($validated['district']) ? strip_tags($validated['district']) : null,
            'investment_budget' => strip_tags($validated['investment_budget']),
            'preferred_location_note' => isset($validated['preferred_location_note']) ? strip_tags($validated['preferred_location_note']) : null,
            'message' => isset($validated['message']) ? strip_tags($validated['message']) : null,
            'kvkk_accepted' => true,
            'status' => 'new',
            'ip_address' => $request->ip(),
        ]);

        // Send notification email if configured
        $notifyEmail = SiteSetting::getValue('franchise_notify_email') ?: SiteSetting::getValue('hq_email');
        if ($notifyEmail && filter_var($notifyEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::raw(
                    "Yeni Soohouse Coffee® Franchise Başvurusu:\n\n"
                    ."Ad Soyad: {$application->full_name}\n"
                    ."Telefon: {$application->phone}\n"
                    ."E-posta: {$application->email}\n"
                    ."Şehir / İlçe: {$application->city} / {$application->district}\n"
                    ."Yatırım Bütçesi: {$application->investment_budget}\n"
                    ."Mesaj: {$application->message}\n",
                    function ($msg) use ($notifyEmail, $application) {
                        $msg->to($notifyEmail)
                            ->subject("Yeni Franchise Başvurusu: {$application->full_name} ({$application->city})");
                    }
                );
            } catch (\Throwable $e) {
                Log::warning('Franchise e-posta bildirimi gönderilemedi: '.$e->getMessage());
            }
        }

        return redirect()->route('public.franchise')
            ->with('franchise_success', 'Franchise başvurunuz başarıyla kaydedilmiştir. Yatırımcı ilişkileri ekibimiz en kısa sürede sizinle iletişime geçecektir.');
    }

    public function press(): View
    {
        $seo = SeoPage::forRoute('press');
        $pressItems = PressItem::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->get();

        return view('public.press', compact('seo', 'pressItems'));
    }

    public function contact(): View
    {
        $seo = SeoPage::forRoute('contact');
        $branches = Branch::where('is_active', true)->orderBy('sort_order')->get();

        return view('public.contact', compact('seo', 'branches'));
    }
}
