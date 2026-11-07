<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\FranchiseApplication;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $branchIds = $user->accessibleBranchIds();

        $branchesQuery = Branch::query()->with(['hours', 'diningTables']);
        if ($branchIds !== null) {
            $branchesQuery->whereIn('id', $branchIds);
        }
        $accessibleBranches = $branchesQuery->orderBy('sort_order')->get();

        $stats = [
            'branches_count' => $branchIds === null ? Branch::count() : count($branchIds),
            'active_branches_count' => $branchIds === null ? Branch::where('is_active', true)->count() : Branch::whereIn('id', $branchIds)->where('is_active', true)->count(),
            'categories_count' => Category::count(),
            'products_count' => Product::count(),
            'new_feedbacks_count' => $user->hasPermission('feedbacks.view')
                ? Feedback::when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))->where('status', 'new')->count()
                : 0,
            'new_franchises_count' => $user->hasPermission('franchises.view')
                ? FranchiseApplication::where('status', 'new')->count()
                : 0,
        ];

        $recentFeedbacks = $user->hasPermission('feedbacks.view')
            ? Feedback::with(['branch', 'diningTable'])
                ->when($branchIds !== null, fn ($q) => $q->whereIn('branch_id', $branchIds))
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        $recentLogs = $user->isSuperAdmin()
            ? AuditLog::with(['branch'])->latest('created_at')->limit(8)->get()
            : collect();

        return view('admin.dashboard', compact('user', 'accessibleBranches', 'stats', 'recentFeedbacks', 'recentLogs'));
    }
}
