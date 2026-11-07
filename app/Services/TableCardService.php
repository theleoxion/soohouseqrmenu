<?php

namespace App\Services;

use App\Models\DiningTable;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;

class TableCardService
{
    /**
     * Available table card design templates.
     *
     * @return array<string, array{name: string, description: string, bg: string, text: string, accent: string, border: string}>
     */
    public static function templates(): array
    {
        return [
            'obsidian' => [
                'name' => 'Obsidian Signature (Koyu Prestij)',
                'description' => 'Derin espresso siyahı zemin, kehribar çerçeve ve beyaz QR alanı.',
                'bg' => '#0D0D0C',
                'text' => '#F8F5F0',
                'accent' => '#C87D3B',
                'border' => '#C87D3B',
            ],
            'cream' => [
                'name' => 'Artisan Cream (Sıcak Kağıt)',
                'description' => 'Sıcak krem zemin, siyah tipografi ve minimal endüstriyel çerçeve.',
                'bg' => '#F8F5F0',
                'text' => '#0D0D0C',
                'accent' => '#C87D3B',
                'border' => '#0D0D0C',
            ],
            'harvest' => [
                'name' => 'Amber Harvest (Hasat Kehribarı)',
                'description' => 'Çift renkli üst başlık, traktör amblemi ve yüksek kontrastlı masa numarası.',
                'bg' => '#FFFFFF',
                'text' => '#1C1B19',
                'accent' => '#3B2618',
                'border' => '#C87D3B',
            ],
        ];
    }

    /**
     * Generate raw SVG markup for just the QR code of a URL.
     */
    public function generateQrSvg(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::H,
            'outputBase64' => false,
            'connectPaths' => true,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'drawLightModules' => false,
        ]);

        return (string) (new QRCode($options))->render($url);
    }

    /**
     * Generate base64 data URI for the QR code SVG (ideal for embedding in Dompdf HTML or IMG tags).
     */
    public function generateQrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::H,
            'outputBase64' => true,
            'connectPaths' => true,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
            'drawLightModules' => true,
        ]);

        return (string) (new QRCode($options))->render($url);
    }

    /**
     * Generate a complete print-ready vector SVG table card (600x840 px, A6 aspect ratio).
     */
    public function generateCardSvg(DiningTable $table, string $templateKey = 'obsidian'): string
    {
        $templates = self::templates();
        $tpl = $templates[$templateKey] ?? $templates['obsidian'];
        $url = $table->qrMenuUrl();

        // Build QR matrix so we can draw crisp SVG modules directly inside our card SVG
        $options = new QROptions([
            'eccLevel' => EccLevel::M,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
        ]);
        $qr = new QRCode($options);
        $qr->addByteSegment($url);
        $matrix = $qr->getQRMatrix();
        $size = $matrix->getSize();

        $qrBoxSize = 320;
        $qrOriginX = 140;
        $qrOriginY = 300;
        $moduleSize = $qrBoxSize / $size;

        $qrPaths = '';
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix->check($x, $y)) {
                    $rx = round($qrOriginX + ($x * $moduleSize), 3);
                    $ry = round($qrOriginY + ($y * $moduleSize), 3);
                    $rw = round($moduleSize + 0.15, 3);
                    $qrPaths .= "<rect x=\"{$rx}\" y=\"{$ry}\" width=\"{$rw}\" height=\"{$rw}\" fill=\"#0D0D0C\"/>";
                }
            }
        }

        $branchName = htmlspecialchars(mb_strtoupper($table->branch->name, 'UTF-8'), ENT_XML1);
        $tableName = htmlspecialchars(mb_strtoupper($table->name, 'UTF-8'), ENT_XML1);
        $tableNumber = htmlspecialchars($table->table_number, ENT_XML1);

        $bg = $tpl['bg'];
        $text = $tpl['text'];
        $accent = $tpl['accent'];
        $border = $tpl['border'];

        $headerBlock = $templateKey === 'harvest'
            ? '<rect x="24" y="24" width="552" height="190" rx="18" fill="#0D0D0C"/>'
            : '';
        $headerTextColor = $templateKey === 'harvest' ? '#F8F5F0' : $text;

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 840" width="600" height="840">
  <defs>
    <style>
      .brand-title { font-family: 'Space Grotesk', 'Segoe UI', Arial, sans-serif; font-weight: 800; letter-spacing: 3px; }
      .branch-sub { font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif; font-weight: 600; letter-spacing: 2px; }
      .table-badge { font-family: 'Space Grotesk', 'Segoe UI', Arial, sans-serif; font-weight: 800; letter-spacing: 1px; }
      .cta-text { font-family: 'Plus Jakarta Sans', 'Segoe UI', Arial, sans-serif; font-weight: 700; letter-spacing: 2.5px; }
    </style>
  </defs>

  <!-- Card Background -->
  <rect width="600" height="840" rx="28" fill="{$bg}"/>
  <rect x="24" y="24" width="552" height="792" rx="18" fill="none" stroke="{$border}" stroke-width="3"/>
  <rect x="34" y="34" width="532" height="772" rx="12" fill="none" stroke="{$accent}" stroke-width="1" stroke-opacity="0.45"/>

  {$headerBlock}

  <!-- Minimal Tractor Emblem Icon -->
  <g transform="translate(260, 56) scale(0.8)" stroke="{$headerTextColor}" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="32" cy="68" r="24"/>
    <circle cx="32" cy="68" r="10"/>
    <circle cx="84" cy="74" r="16"/>
    <circle cx="84" cy="74" r="6"/>
    <path d="M28 40 C28 28, 38 24, 78 24 C92 24, 98 34, 98 52"/>
    <line x1="78" y1="24" x2="78" y2="4"/>
    <line x1="28" y1="30" x2="18" y2="20"/>
    <line x1="14" y1="24" x2="24" y2="14"/>
    <line x1="46" y1="68" x2="66" y2="68"/>
  </g>

  <!-- Brand Plate -->
  <rect x="130" y="138" width="340" height="42" rx="6" fill="#0D0D0C" stroke="{$accent}" stroke-width="1.5"/>
  <text x="300" y="166" text-anchor="middle" fill="#FFFFFF" font-size="22" class="brand-title">SOOHOUSECOFFEE®</text>

  <!-- Branch Name -->
  <text x="300" y="206" text-anchor="middle" fill="{$accent}" font-size="13" class="branch-sub">{$branchName}</text>

  <!-- Table Name / Number Pill -->
  <rect x="180" y="226" width="240" height="46" rx="23" fill="{$accent}"/>
  <text x="300" y="256" text-anchor="middle" fill="#FFFFFF" font-size="20" class="table-badge">{$tableName} (#{$tableNumber})</text>

  <!-- White QR Frame -->
  <rect x="124" y="284" width="352" height="352" rx="20" fill="#FFFFFF" stroke="{$border}" stroke-width="2"/>
  {$qrPaths}

  <!-- Call to Action -->
  <text x="300" y="688" text-anchor="middle" fill="{$text}" font-size="18" class="cta-text">MENÜ İÇİN OKUTUN</text>
  <line x1="220" y1="708" x2="380" y2="708" stroke="{$accent}" stroke-width="2"/>
  <text x="300" y="736" text-anchor="middle" fill="{$text}" fill-opacity="0.75" font-size="13" class="branch-sub">DİJİTAL QR MENÜ &amp; GERİ BİLDİRİM</text>
  <text x="300" y="782" text-anchor="middle" fill="{$accent}" font-size="11" class="branch-sub">TOPRAKTAN FİNCANA NİTELİKLİ KAHVE</text>
</svg>
SVG;
    }

    /**
     * Generate a high-resolution print-ready PNG table card (900x1260 px) using PHP GD (no Imagick required).
     */
    public function generateCardPng(DiningTable $table, string $templateKey = 'obsidian'): string
    {
        $templates = self::templates();
        $tpl = $templates[$templateKey] ?? $templates['obsidian'];
        $url = $table->qrMenuUrl();

        $width = 900;
        $height = 1260;
        $im = imagecreatetruecolor($width, $height);
        imageantialias($im, true);

        [$bgR, $bgG, $bgB] = $this->hexToRgb($tpl['bg']);
        [$txtR, $txtG, $txtB] = $this->hexToRgb($tpl['text']);
        [$accR, $accG, $accB] = $this->hexToRgb($tpl['accent']);
        [$brdR, $brdG, $brdB] = $this->hexToRgb($tpl['border']);

        $bgColor = imagecolorallocate($im, $bgR, $bgG, $bgB);
        $textColor = imagecolorallocate($im, $txtR, $txtG, $txtB);
        $accentColor = imagecolorallocate($im, $accR, $accG, $accB);
        $borderColor = imagecolorallocate($im, $brdR, $brdG, $brdB);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 13, 13, 12);

        imagefilledrectangle($im, 0, 0, $width, $height, $bgColor);

        // Outer border
        imagesetthickness($im, 6);
        imagerectangle($im, 36, 36, $width - 36, $height - 36, $borderColor);
        imagesetthickness($im, 2);
        imagerectangle($im, 52, 52, $width - 52, $height - 52, $accentColor);

        // Copy brand logo from public/brand/soohouse-logo-640.png if available
        $logoPath = public_path('brand/soohouse-logo-640.png');
        if (file_exists($logoPath)) {
            $logoIm = @imagecreatefrompng($logoPath);
            if ($logoIm) {
                $lw = imagesx($logoIm);
                $lh = imagesy($logoIm);
                $targetW = 420;
                $targetH = (int) round($lh * ($targetW / $lw));
                $destX = (int) (($width - $targetW) / 2);
                $destY = 85;

                if ($templateKey === 'obsidian') {
                    imagefilledrectangle($im, $destX - 24, $destY - 16, $destX + $targetW + 24, $destY + $targetH + 16, $white);
                }

                imagecopyresampled($im, $logoIm, $destX, $destY, 0, 0, $targetW, $targetH, $lw, $lh);
                imagedestroy($logoIm);
            }
        }

        // Accent bar for table number
        imagefilledrectangle($im, 200, 340, 700, 415, $accentColor);

        // Draw QR code matrix on a crisp white square
        $qrFrameX = 190;
        $qrFrameY = 450;
        $qrFrameSize = 520;
        imagefilledrectangle($im, $qrFrameX, $qrFrameY, $qrFrameX + $qrFrameSize, $qrFrameY + $qrFrameSize, $white);
        imagerectangle($im, $qrFrameX, $qrFrameY, $qrFrameX + $qrFrameSize, $qrFrameY + $qrFrameSize, $borderColor);

        $options = new QROptions([
            'eccLevel' => EccLevel::H,
            'addQuietzone' => true,
            'quietzoneSize' => 2,
        ]);
        $qr = new QRCode($options);
        $qr->addByteSegment($url);
        $matrix = $qr->getQRMatrix();
        $mSize = $matrix->getSize();
        $innerSize = 460;
        $offsetX = $qrFrameX + 30;
        $offsetY = $qrFrameY + 30;
        $cellSize = $innerSize / $mSize;

        for ($y = 0; $y < $mSize; $y++) {
            for ($x = 0; $x < $mSize; $x++) {
                if ($matrix->check($x, $y)) {
                    $x1 = (int) round($offsetX + ($x * $cellSize));
                    $y1 = (int) round($offsetY + ($y * $cellSize));
                    $x2 = (int) round($offsetX + (($x + 1) * $cellSize));
                    $y2 = (int) round($offsetY + (($y + 1) * $cellSize));
                    imagefilledrectangle($im, $x1, $y1, $x2, $y2, $black);
                }
            }
        }

        $tableLabel = $this->asciiSafe($table->name.' (#'.$table->table_number.')');
        $ctaLabel = 'MENU ICIN QR KODU OKUTUN';
        $branchLabel = $this->asciiSafe(mb_strtoupper($table->branch->name, 'UTF-8'));

        $this->drawCenteredGdString($im, 5, 368, $tableLabel, $white, $width);
        $this->drawCenteredGdString($im, 5, 1020, $ctaLabel, $textColor, $width);
        imagefilledrectangle($im, 320, 1060, 580, 1064, $accentColor);
        $this->drawCenteredGdString($im, 4, 1090, $branchLabel, $accentColor, $width);
        $this->drawCenteredGdString($im, 3, 1140, 'SOOHOUSE COFFEE - DIJITAL QR MENU', $textColor, $width);

        ob_start();
        imagepng($im, null, 6);
        $pngBinary = (string) ob_get_clean();
        imagedestroy($im);

        return $pngBinary;
    }

    /**
     * Generate a print-ready PDF for a single table or a collection of tables (batch print).
     *
     * @param  Collection<int, DiningTable>  $tables
     */
    public function generatePdf(Collection $tables, string $templateKey = 'obsidian'): string
    {
        $templates = self::templates();
        $tpl = $templates[$templateKey] ?? $templates['obsidian'];

        $cards = $tables->map(function (DiningTable $table) {
            return [
                'table' => $table,
                'branch' => $table->branch,
                'qr_data_uri' => $this->generateQrDataUri($table->qrMenuUrl()),
                'url' => $table->qrMenuUrl(),
            ];
        });

        $html = view('admin.tables.pdf-cards', [
            'cards' => $cards,
            'template' => $tpl,
            'templateKey' => $templateKey,
        ])->render();

        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    protected function drawCenteredGdString(\GdImage $im, int $font, int $y, string $text, int $color, int $canvasWidth): void
    {
        $fontWidth = imagefontwidth($font);
        $textWidth = $fontWidth * strlen($text);
        $x = (int) max(20, ($canvasWidth - $textWidth) / 2);
        imagestring($im, $font, $x, $y, $text, $color);
    }

    protected function asciiSafe(string $text): string
    {
        $map = [
            'ç' => 'c', 'Ç' => 'C',
            'ğ' => 'g', 'Ğ' => 'G',
            'ı' => 'i', 'I' => 'I',
            'İ' => 'I', 'i' => 'i',
            'ö' => 'o', 'Ö' => 'O',
            'ş' => 's', 'Ş' => 'S',
            'ü' => 'u', 'Ü' => 'U',
        ];

        return strtr($text, $map);
    }
}
