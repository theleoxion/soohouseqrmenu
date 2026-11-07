<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Feedback;
use App\Services\AuditLogger;
use App\Services\ExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function __construct(protected ExcelService $excelService) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $branchIds = $user->accessibleBranchIds();

        $branches = Branch::when($branchIds !== null, fn ($q) => $q->whereIn('id', $branchIds))
            ->orderBy('name')
            ->get();

        $feedbacks = $this->filteredQuery($request, $branchIds)
            ->orderByDesc('submitted_date')
            ->orderByDesc('submitted_time')
            ->paginate(25)
            ->withQueryString();

        return view('admin.feedbacks.index', compact('feedbacks', 'branches'));
    }

    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $user = $request->user();
        if (! $user->canAccessBranch($feedback->branch_id)) {
            abort(403, 'Bu şubenin geri bildirimlerine erişim yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,in_review,resolved'],
            'internal_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldValues = $feedback->only(['status', 'internal_note']);
        $feedback->update($validated);

        AuditLogger::record(
            actionType: 'feedback.updated',
            description: "#{$feedback->id} numaralı müşteri geri bildirimi güncellendi ({$feedback->statusLabel()}).",
            auditable: $feedback,
            oldValues: $oldValues,
            newValues: $validated,
            branchId: $feedback->branch_id
        );

        return back()->with('status', 'Geri bildirim durumu ve iç notu güncellendi.');
    }

    public function export(Request $request): Response
    {
        $user = $request->user();
        $branchIds = $user->accessibleBranchIds();

        $feedbacks = $this->filteredQuery($request, $branchIds)
            ->orderByDesc('submitted_date')
            ->orderByDesc('submitted_time')
            ->get();

        $bytes = $this->excelService->exportFeedbacks($feedbacks);
        $filename = 'soohouse-geri-bildirimler-'.now()->format('Y-m-d').'.xlsx';

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function filteredQuery(Request $request, ?array $branchIds)
    {
        $query = Feedback::with(['branch', 'diningTable']);

        if ($branchIds !== null) {
            $query->whereIn('branch_id', $branchIds);
        }

        if ($request->filled('branch_id')) {
            $reqBranch = $request->integer('branch_id');
            if ($branchIds === null || in_array($reqBranch, $branchIds, true)) {
                $query->where('branch_id', $reqBranch);
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_date', '<=', $request->input('date_to'));
        }

        return $query;
    }
}
