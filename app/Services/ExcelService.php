<?php

namespace App\Services;

use App\Helpers\TurkishSlug;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\FranchiseApplication;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelService
{
    public function __construct(protected PriceCalculationService $priceService) {}

    /**
     * Generate downloadable Excel template for product import.
     */
    public function generateProductImportTemplate(): string
    {
        $headers = [
            'SKU (Zorunlu - Benzersiz Kod)',
            'Ürün Adı (Zorunlu)',
            'Kategori Adı (Zorunlu)',
            'Merkez Fiyatı ₺ (Zorunlu)',
            'Alt Fiyat Sınırı ₺ (Opsiyonel)',
            'Üst Fiyat Sınırı ₺ (Opsiyonel)',
            'Kısa Açıklama',
            'Detaylı Açıklama',
            'Etiketler (virgülle: signature,new,vegan,vegetarian,lactose_free,sugar_free,gluten_free)',
            'Sıcak/Soğuk (virgülle: hot,cold)',
            'Çekirdek Kökeni (Opsiyonel)',
            'Kavrum Derecesi (Opsiyonel)',
            'Tadım Notları (Opsiyonel)',
            'Aktif (1 veya 0)',
            'Sıra No',
        ];

        $sampleGuideRow = [
            'SH-ESP-001',
            'Soohouse Signature Espresso',
            'Espresso Bazlı Kahveler',
            115.00,
            95.00,
            160.00,
            'Çiftlikten fincana yoğun gövdeli imza espresso.',
            '%100 Arabica çekirdeklerinden orta-koyu kavrum.',
            'signature,sugar_free',
            'hot,cold',
            'Kolombiya & Brezilya',
            'Orta-Koyu (Full City)',
            'Bitter Çikolata, Karamel, Kavrulmuş Fındık',
            1,
            1,
        ];

        return $this->buildWorkbookBytes(
            title: 'Urun_Aktarim_Sablonu',
            headers: $headers,
            rows: [$sampleGuideRow],
            currencyColumns: [4, 5, 6]
        );
    }

    /**
     * Parse and validate an uploaded Excel file for product import, returning preview rows & row-by-row errors.
     *
     * @return array{valid_rows: array<int, array<string, mixed>>, errors: array<int, array{row: int, sku: string, message: string}>, total_rows: int}
     */
    public function validateAndPreviewProductImport(UploadedFile|string $filePath): array
    {
        $path = $filePath instanceof UploadedFile ? $filePath->getRealPath() : $filePath;
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, false);

        $validRows = [];
        $errors = [];
        $seenSkus = [];

        foreach ($rawRows as $index => $row) {
            // Skip header row (index 0)
            if ($index === 0) {
                continue;
            }

            $rowNumber = $index + 1;
            $sku = trim((string) ($row[0] ?? ''));
            $name = trim((string) ($row[1] ?? ''));
            $categoryName = trim((string) ($row[2] ?? ''));
            $basePriceRaw = $row[3] ?? null;

            // Skip completely blank rows
            if ($sku === '' && $name === '' && $categoryName === '' && ($basePriceRaw === null || $basePriceRaw === '')) {
                continue;
            }

            $rowErrors = [];

            if ($sku === '') {
                $rowErrors[] = 'SKU (Ürün Kodu) zorunludur.';
            } elseif (isset($seenSkus[strtoupper($sku)])) {
                $rowErrors[] = "Dosya içinde mükerrer SKU: {$sku}.";
            } else {
                $seenSkus[strtoupper($sku)] = true;
            }

            if ($name === '') {
                $rowErrors[] = 'Ürün Adı zorunludur.';
            }

            if ($categoryName === '') {
                $rowErrors[] = 'Kategori Adı zorunludur.';
            }

            $basePrice = $this->parsePrice($basePriceRaw);
            if ($basePrice === null || $basePrice < 0) {
                $rowErrors[] = 'Merkez Fiyatı geçerli bir pozitif sayı olmalıdır.';
            }

            $minPrice = $this->parsePrice($row[4] ?? null);
            $maxPrice = $this->parsePrice($row[5] ?? null);

            if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
                $rowErrors[] = 'Alt fiyat sınırı üst fiyat sınırından büyük olamaz.';
            }

            if (! empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNumber,
                    'sku' => $sku ?: '-',
                    'message' => implode(' ', $rowErrors),
                ];

                continue;
            }

            $existingProduct = Product::where('sku', $sku)->first();

            $tags = array_values(array_filter(array_map('trim', explode(',', (string) ($row[8] ?? '')))));
            $temps = array_values(array_filter(array_map('trim', explode(',', (string) ($row[9] ?? '')))));

            $validRows[] = [
                'row' => $rowNumber,
                'action' => $existingProduct ? 'update' : 'create',
                'sku' => strtoupper($sku),
                'name' => $name,
                'category_name' => $categoryName,
                'base_price' => $basePrice,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'short_description' => trim((string) ($row[6] ?? '')) ?: null,
                'description' => trim((string) ($row[7] ?? '')) ?: null,
                'tags' => $tags,
                'temperature_options' => $temps ?: ['hot'],
                'bean_origin' => trim((string) ($row[10] ?? '')) ?: null,
                'roast_level' => trim((string) ($row[11] ?? '')) ?: null,
                'tasting_notes' => trim((string) ($row[12] ?? '')) ?: null,
                'is_active' => isset($row[13]) && (string) $row[13] !== '' ? (bool) (int) $row[13] : true,
                'sort_order' => isset($row[14]) && is_numeric($row[14]) ? (int) $row[14] : 0,
            ];
        }

        return [
            'valid_rows' => $validRows,
            'errors' => $errors,
            'total_rows' => count($validRows) + count($errors),
        ];
    }

    /**
     * Upsert validated product rows into database and record audit log.
     *
     * @param  array<int, array<string, mixed>>  $validRows
     * @return array{created: int, updated: int}
     */
    public function commitProductImport(array $validRows): array
    {
        $created = 0;
        $updated = 0;

        DB::transaction(function () use ($validRows, &$created, &$updated) {
            foreach ($validRows as $item) {
                $categorySlug = TurkishSlug::make($item['category_name']);
                $category = Category::firstOrCreate(
                    ['slug' => $categorySlug],
                    ['name' => $item['category_name'], 'is_active' => true, 'sort_order' => 0]
                );

                $existing = Product::where('sku', $item['sku'])->first();
                $slug = $existing?->slug ?? $this->uniqueProductSlug($item['name']);

                $payload = [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'slug' => $slug,
                    'base_price' => $item['base_price'],
                    'min_price' => $item['min_price'],
                    'max_price' => $item['max_price'],
                    'short_description' => $item['short_description'],
                    'description' => $item['description'],
                    'tags' => $item['tags'],
                    'temperature_options' => $item['temperature_options'],
                    'bean_origin' => $item['bean_origin'],
                    'roast_level' => $item['roast_level'],
                    'tasting_notes' => $item['tasting_notes'],
                    'is_active' => $item['is_active'],
                    'sort_order' => $item['sort_order'],
                ];

                if ($existing) {
                    $existing->update($payload);
                    $updated++;
                } else {
                    Product::create(array_merge(['sku' => $item['sku']], $payload));
                    $created++;
                }
            }

            AuditLogger::record(
                actionType: 'product.excel_imported',
                description: "Excel ile toplu ürün aktarımı tamamlandı ({$created} yeni, {$updated} güncellendi).",
                newValues: ['created' => $created, 'updated' => $updated]
            );
        });

        return ['created' => $created, 'updated' => $updated];
    }

    /**
     * Export Central Product Pool to Excel (.xlsx).
     */
    public function exportProducts(): string
    {
        $products = Product::with('category')->orderBy('category_id')->orderBy('sort_order')->get();

        $headers = [
            'SKU',
            'Ürün Adı',
            'Kategori',
            'Merkez Fiyatı (₺)',
            'Alt Sınır (₺)',
            'Üst Sınır (₺)',
            'Etiketler',
            'Sıcak/Soğuk',
            'Çekirdek Kökeni',
            'Kavrum',
            'Tadım Notları',
            'Durum',
            'Güncellenme Tarihi',
        ];

        $rows = $products->map(fn (Product $p) => [
            $p->sku,
            $p->name,
            $p->category?->name ?? '-',
            (float) $p->base_price,
            $p->min_price !== null ? (float) $p->min_price : null,
            $p->max_price !== null ? (float) $p->max_price : null,
            implode(', ', $p->tags ?? []),
            implode(', ', $p->temperature_options ?? []),
            $p->bean_origin ?? '-',
            $p->roast_level ?? '-',
            $p->tasting_notes ?? '-',
            $p->is_active ? 'Aktif' : 'Pasif',
            $p->updated_at?->format('d.m.Y H:i') ?? '-',
        ])->all();

        return $this->buildWorkbookBytes('Soohouse_Urun_Havuzu', $headers, $rows, [4, 5, 6]);
    }

    /**
     * Export Branch-specific Price List to Excel (.xlsx).
     */
    public function exportBranchPrices(?int $branchId = null): string
    {
        $branches = $branchId ? Branch::where('id', $branchId)->get() : Branch::orderBy('name')->get();
        $products = Product::with('category')->orderBy('category_id')->orderBy('sort_order')->get();

        $headers = [
            'Şube',
            'SKU',
            'Kategori',
            'Ürün Adı',
            'Merkez Fiyatı (₺)',
            'Şube Özel Fiyatı (₺)',
            'İndirim Bilgisi',
            'Müşteri Son Fiyatı (₺)',
            'Menüde Aktif',
            'Tükendi Durumu',
        ];

        $rows = [];
        foreach ($branches as $branch) {
            $pivots = BranchProduct::where('branch_id', $branch->id)->get()->keyBy('product_id');

            foreach ($products as $product) {
                /** @var BranchProduct|null $pivot */
                $pivot = $pivots->get($product->id);
                $calc = $this->priceService->calculate($product, $pivot);

                $rows[] = [
                    $branch->name,
                    $product->sku,
                    $product->category?->name ?? '-',
                    $product->name,
                    $calc['base_price'],
                    $pivot?->custom_price !== null ? (float) $pivot->custom_price : null,
                    $calc['discount_label'] ?? 'Yok',
                    $calc['final_price'],
                    ($pivot ? $pivot->is_available : false) ? 'Evet' : 'Hayır',
                    ($pivot && $pivot->is_sold_out) ? 'Tükendi' : 'Stokta',
                ];
            }
        }

        return $this->buildWorkbookBytes('Soohouse_Sube_Fiyat_Listesi', $headers, $rows, [5, 6, 8]);
    }

    /**
     * Export Franchise Applications to Excel (.xlsx).
     */
    public function exportFranchises(Collection $applications): string
    {
        $headers = [
            'Başvuru No',
            'Ad Soyad',
            'Telefon',
            'E-posta',
            'Şehir',
            'İlçe',
            'Yatırım Bütçesi',
            'Mesaj',
            'Durum',
            'İç Notlar',
            'KVKK Onayı',
            'Başvuru Tarihi',
        ];

        $rows = $applications->map(fn (FranchiseApplication $app) => [
            $app->id,
            $app->full_name,
            $app->phone,
            $app->email,
            $app->city,
            $app->district ?? '-',
            $app->investment_budget,
            $app->message ?? '-',
            $app->statusLabel(),
            $app->internal_notes ?? '-',
            $app->kvkk_accepted ? 'Onaylandı' : 'Hayır',
            $app->created_at?->format('d.m.Y H:i') ?? '-',
        ])->all();

        return $this->buildWorkbookBytes('Soohouse_Franchise_Basvurulari', $headers, $rows);
    }

    /**
     * Export Customer Feedbacks to Excel (.xlsx).
     */
    public function exportFeedbacks(Collection $feedbacks): string
    {
        $headers = [
            'Kayıt No',
            'Şube',
            'Masa',
            'Tür',
            'Mesaj',
            'Tarih',
            'Gün',
            'Saat',
            'Durum',
            'İç Not',
        ];

        $rows = $feedbacks->map(fn (Feedback $f) => [
            $f->id,
            $f->branch?->name ?? '-',
            $f->diningTable ? "{$f->diningTable->name} (#{$f->diningTable->table_number})" : 'Belirtilmedi',
            $f->typeLabel(),
            $f->message,
            $f->submitted_date?->format('d.m.Y') ?? '-',
            $f->day_name,
            substr((string) $f->submitted_time, 0, 5),
            $f->statusLabel(),
            $f->internal_note ?? '-',
        ])->all();

        return $this->buildWorkbookBytes('Soohouse_Musteri_Geri_Bildirimleri', $headers, $rows);
    }

    /**
     * Export Immutable Audit Logs to Excel (.xlsx).
     */
    public function exportAuditLogs(Collection $logs): string
    {
        $headers = [
            'Log ID',
            'Tarih / Saat',
            'Kullanıcı',
            'Şube',
            'İşlem Türü',
            'Açıklama',
            'Eski Değerler',
            'Yeni Değerler',
            'IP Adresi',
        ];

        $rows = $logs->map(fn (AuditLog $log) => [
            $log->id,
            $log->created_at?->format('d.m.Y H:i:s') ?? '-',
            $log->user_name ?? 'Sistem',
            $log->branch?->name ?? 'Genel Merkez',
            $log->actionLabel(),
            $log->description,
            $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '-',
            $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '-',
            $log->ip_address ?? '-',
        ])->all();

        return $this->buildWorkbookBytes('Soohouse_Denetim_Kayitlari', $headers, $rows);
    }

    /**
     * Build a styled .xlsx workbook and return binary bytes.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int>  $currencyColumns  1-based column indices to format as Turkish Lira
     */
    protected function buildWorkbookBytes(string $title, array $headers, array $rows, array $currencyColumns = []): string
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('Soohouse Coffee® Yönetim Platformu')
            ->setTitle($title);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31, 'UTF-8'));

        // Header Row
        foreach ($headers as $colIdx => $headerText) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}1", $headerText);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D0D0C']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Data Rows
        foreach ($rows as $rowIdx => $rowValues) {
            $excelRow = $rowIdx + 2;
            foreach ($rowValues as $colIdx => $val) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $sheet->setCellValue("{$colLetter}{$excelRow}", $val);
            }
        }

        $totalRows = count($rows) + 1;
        if ($totalRows > 1 && ! empty($currencyColumns)) {
            foreach ($currencyColumns as $colIndex1Based) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex1Based);
                $sheet->getStyle("{$colLetter}2:{$colLetter}{$totalRows}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00 "₺"');
            }
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');

        return (string) ob_get_clean();
    }

    protected function parsePrice(mixed $val): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }

        if (is_numeric($val)) {
            return round((float) $val, 2);
        }

        $cleaned = str_replace(['₺', 'TL', ' '], '', (string) $val);
        if (str_contains($cleaned, ',') && str_contains($cleaned, '.')) {
            $cleaned = str_replace('.', '', $cleaned);
            $cleaned = str_replace(',', '.', $cleaned);
        } elseif (str_contains($cleaned, ',')) {
            $cleaned = str_replace(',', '.', $cleaned);
        }

        return is_numeric($cleaned) ? round((float) $cleaned, 2) : null;
    }

    protected function uniqueProductSlug(string $name): string
    {
        $base = TurkishSlug::make($name);
        $slug = $base;
        $counter = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
