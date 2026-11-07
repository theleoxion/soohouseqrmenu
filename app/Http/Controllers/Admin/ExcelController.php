<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\ExcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ExcelController extends Controller
{
    public function __construct(protected ExcelService $excelService) {}

    public function index(): View
    {
        $branches = Branch::orderBy('name')->get();

        return view('admin.excel.index', [
            'branches' => $branches,
            'previewResult' => session('excel_preview_result'),
        ]);
    }

    public function downloadTemplate(): Response
    {
        $bytes = $this->excelService->generateProductImportTemplate();

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="soohouse-urun-yukleme-sablonu.xlsx"',
        ]);
    }

    public function previewImport(Request $request): RedirectResponse
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $result = $this->excelService->validateAndPreviewProductImport($request->file('excel_file'));

        // Store validated rows in session for confirmation step
        $request->session()->put('excel_import_valid_rows', $result['valid_rows']);

        return redirect()->route('admin.excel.index')
            ->with('excel_preview_result', $result);
    }

    public function commitImport(Request $request): RedirectResponse
    {
        $validRows = $request->session()->pull('excel_import_valid_rows', []);

        if (empty($validRows)) {
            return redirect()->route('admin.excel.index')
                ->withErrors(['excel_file' => 'Onaylanacak geçerli ürün satırı bulunamadı. Lütfen dosyayı tekrar yükleyin.']);
        }

        $summary = $this->excelService->commitProductImport($validRows);

        return redirect()->route('admin.excel.index')
            ->with('status', "Excel aktarımı tamamlandı: {$summary['created']} yeni ürün eklendi, {$summary['updated']} mevcut ürün güncellendi.");
    }

    public function exportProducts(): Response
    {
        $bytes = $this->excelService->exportProducts();
        $filename = 'soohouse-urun-havuzu-'.now()->format('Y-m-d').'.xlsx';

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportBranchPrices(Request $request): Response
    {
        $branchId = $request->filled('branch_id') ? $request->integer('branch_id') : null;
        $bytes = $this->excelService->exportBranchPrices($branchId);
        $filename = 'soohouse-sube-fiyat-listesi-'.now()->format('Y-m-d').'.xlsx';

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
