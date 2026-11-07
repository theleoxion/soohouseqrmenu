<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(protected ExcelService $excelService) {}

    public function index(Request $request): View
    {
        $users = User::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $actionTypes = AuditLog::actionTypeLabels();

        $logs = $this->filteredQuery($request)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs.index', compact('logs', 'users', 'branches', 'actionTypes'));
    }

    public function export(Request $request): Response
    {
        $logs = $this->filteredQuery($request)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5000)
            ->get();

        $bytes = $this->excelService->exportAuditLogs($logs);
        $filename = 'soohouse-denetim-kayitlari-'.now()->format('Y-m-d').'.xlsx';

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function filteredQuery(Request $request)
    {
        $query = AuditLog::with(['user', 'branch']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->input('action_type'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return $query;
    }
}
