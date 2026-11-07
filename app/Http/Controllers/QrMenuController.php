<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Feedback;
use App\Services\PriceCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class QrMenuController extends Controller
{
    public function __construct(protected PriceCalculationService $priceService) {}

    /**
     * Public QR Menu without table token (table is treated as "Belirtilmedi").
     */
    public function show(Branch $branch): View
    {
        return $this->renderMenu($branch, null);
    }

    /**
     * Table-specific QR Menu via cryptographic table token (noindex + canonical to branch menu).
     */
    public function showTable(Branch $branch, string $token): View
    {
        $table = DiningTable::where('branch_id', $branch->id)
            ->where('token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->renderMenu($branch, $table);
    }

    protected function renderMenu(Branch $branch, ?DiningTable $table): View
    {
        if (! $branch->is_active) {
            abort(404);
        }

        $branch->load(['hours', 'specialDays']);
        $openStatus = $branch->openStatus();

        // Load branch product pivots where is_available = true
        $pivots = BranchProduct::where('branch_id', $branch->id)
            ->where('is_available', true)
            ->get()
            ->keyBy('product_id');

        $categories = Category::where('is_active', true)
            ->with(['products' => function ($query) use ($pivots) {
                $query->where('is_active', true)
                    ->whereIn('id', $pivots->keys()->all())
                    ->orderBy('sort_order')
                    ->orderBy('id');
            }])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Category $cat) => $cat->products->isNotEmpty())
            ->values();

        // Attach computed price details & sold_out flag to each product
        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                /** @var BranchProduct|null $pivot */
                $pivot = $pivots->get($product->id);
                $product->setAttribute('calculated_price', $this->priceService->calculate($product, $pivot));
                $product->setAttribute('is_sold_out', (bool) ($pivot?->is_sold_out ?? false));
            }
        }

        return view('public.qrmenu', [
            'branch' => $branch,
            'table' => $table,
            'openStatus' => $openStatus,
            'categories' => $categories,
            'isTableSpecific' => $table !== null,
        ]);
    }

    /**
     * Store customer Request / Suggestion / Complaint from QR Menu.
     */
    public function storeFeedback(Request $request, Branch $branch): RedirectResponse
    {
        if (! $branch->is_active) {
            abort(404);
        }

        // Honeypot spam protection
        if ($request->filled('website_trap')) {
            return back()->with('feedback_thank_you', true);
        }

        $throttleKey = 'qrmenu-feedback|'.$branch->id.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'message' => 'Çok sık bildirim gönderdiniz. Lütfen birkaç dakika bekleyip tekrar deneyin.',
            ])->withInput();
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:request,suggestion,complaint'],
            'message' => ['required', 'string', 'min:5', 'max:1500'],
            'table_token' => ['nullable', 'string', 'max:64'],
        ]);

        RateLimiter::hit($throttleKey, 180);

        $diningTable = null;
        if (! empty($validated['table_token'])) {
            $diningTable = DiningTable::where('branch_id', $branch->id)
                ->where('token', $validated['table_token'])
                ->where('is_active', true)
                ->first();
        }

        $now = Carbon::now('Europe/Istanbul');
        $turkishDays = [
            1 => 'Pazartesi',
            2 => 'Salı',
            3 => 'Çarşamba',
            4 => 'Perşembe',
            5 => 'Cuma',
            6 => 'Cumartesi',
            7 => 'Pazar',
        ];

        $cleanMessage = trim(strip_tags($validated['message']));

        Feedback::create([
            'branch_id' => $branch->id,
            'dining_table_id' => $diningTable?->id,
            'type' => $validated['type'],
            'message' => $cleanMessage,
            'status' => 'new',
            'submitted_date' => $now->toDateString(),
            'submitted_time' => $now->format('H:i:s'),
            'day_name' => $turkishDays[(int) $now->isoWeekday()] ?? 'Bilinmiyor',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return back()->with('feedback_thank_you', true);
    }
}
