<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\DiningTable;
use App\Services\AuditLogger;
use App\Services\TableCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TableController extends Controller
{
    public function __construct(protected TableCardService $cardService) {}

    public function index(Branch $branch): View
    {
        $tables = $branch->diningTables()->orderBy('sort_order')->orderBy('id')->get();
        $templates = TableCardService::templates();

        return view('admin.tables.index', compact('branch', 'tables', 'templates'));
    }

    public function store(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'table_number' => ['required', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $table = DiningTable::create([
            'branch_id' => $branch->id,
            'name' => $validated['name'],
            'table_number' => $validated['table_number'],
            'token' => DiningTable::generateUniqueToken(),
            'is_active' => true,
            'sort_order' => (int) ($validated['sort_order'] ?? ($branch->diningTables()->max('sort_order') + 1)),
        ]);

        AuditLogger::record(
            actionType: 'table.created',
            description: "{$branch->name} şubesine yeni masa eklendi: {$table->name} (#{$table->table_number})",
            auditable: $table,
            newValues: $table->toArray(),
            branchId: $branch->id
        );

        return back()->with('status', "{$table->name} (#{$table->table_number}) benzersiz QR token ile oluşturuldu.");
    }

    public function update(Request $request, Branch $branch, DiningTable $table): RedirectResponse
    {
        if ($table->branch_id !== $branch->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'table_number' => ['required', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $oldValues = $table->only(['name', 'table_number', 'is_active']);

        $table->update([
            'name' => $validated['name'],
            'table_number' => $validated['table_number'],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($validated['sort_order'] ?? $table->sort_order),
        ]);

        AuditLogger::record(
            actionType: 'table.updated',
            description: "{$branch->name} - {$table->name} masa bilgileri güncellendi.",
            auditable: $table,
            oldValues: $oldValues,
            newValues: $table->only(['name', 'table_number', 'is_active']),
            branchId: $branch->id
        );

        return back()->with('status', "{$table->name} güncellendi.");
    }

    public function destroy(Branch $branch, DiningTable $table): RedirectResponse
    {
        if ($table->branch_id !== $branch->id) {
            abort(403);
        }

        $name = $table->name;
        $table->delete();

        AuditLogger::record(
            actionType: 'table.deleted',
            description: "{$branch->name} şubesinden {$name} masası silindi.",
            branchId: $branch->id
        );

        return back()->with('status', "{$name} silindi.");
    }

    /**
     * Download single table card in SVG, PNG, or PDF format.
     */
    public function downloadCard(Request $request, Branch $branch, DiningTable $table, string $format): Response
    {
        if ($table->branch_id !== $branch->id) {
            abort(403);
        }

        $templateKey = $request->query('template', 'obsidian');
        $safeFilename = "soohouse-{$branch->slug}-masa-{$table->table_number}-{$templateKey}";

        return match (strtolower($format)) {
            'svg' => response($this->cardService->generateCardSvg($table, $templateKey), 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => "attachment; filename=\"{$safeFilename}.svg\"",
            ]),
            'png' => response($this->cardService->generateCardPng($table, $templateKey), 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => "attachment; filename=\"{$safeFilename}.png\"",
            ]),
            'pdf' => response($this->cardService->generatePdf(collect([$table]), $templateKey), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$safeFilename}.pdf\"",
            ]),
            default => abort(404),
        };
    }

    /**
     * Download all active tables of the branch in a single print-ready PDF.
     */
    public function downloadBatchPdf(Request $request, Branch $branch): Response|RedirectResponse
    {
        $templateKey = $request->query('template', 'obsidian');
        $tables = $branch->diningTables()->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        if ($tables->isEmpty()) {
            return back()->withErrors(['name' => 'Toplu PDF çıktısı almak için önce en az 1 aktif masa eklemelisiniz.']);
        }

        $pdfBytes = $this->cardService->generatePdf($tables, $templateKey);
        $filename = "soohouse-{$branch->slug}-tum-masalar-{$templateKey}.pdf";

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
