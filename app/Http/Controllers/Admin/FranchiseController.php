<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FranchiseApplication;
use App\Services\AuditLogger;
use App\Services\ExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FranchiseController extends Controller
{
    public function __construct(protected ExcelService $excelService) {}

    public function index(Request $request): View
    {
        $applications = $this->filteredQuery($request)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.franchises.index', compact('applications'));
    }

    public function update(Request $request, FranchiseApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,called,interviewed,positive,negative'],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $oldValues = $application->only(['status', 'internal_notes']);
        $application->update($validated);

        AuditLogger::record(
            actionType: 'franchise.updated',
            description: "{$application->full_name} ({$application->city}) franchise başvurusu güncellendi: {$application->statusLabel()}",
            auditable: $application,
            oldValues: $oldValues,
            newValues: $validated
        );

        return back()->with('status', "{$application->full_name} başvurusunun durumu ve notları güncellendi.");
    }

    public function export(Request $request): Response
    {
        $applications = $this->filteredQuery($request)->latest()->get();
        $bytes = $this->excelService->exportFranchises($applications);
        $filename = 'soohouse-franchise-basvurulari-'.now()->format('Y-m-d').'.xlsx';

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function filteredQuery(Request $request)
    {
        $query = FranchiseApplication::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->input('city').'%');
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return $query;
    }
}
