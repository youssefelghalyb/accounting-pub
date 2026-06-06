<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Models\SalesInvoice;
use Modules\Settings\Models\OrganizationSetting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportSalesInvoice
{
    // Palette
    const NAVY       = 'FF1E3A5F';
    const BLUE       = 'FF2563EB';
    const SILVER     = 'FFF1F5F9';
    const GRAY_ROW   = 'FFF8FAFC';
    const WHITE      = 'FFFFFFFF';
    const DARK       = 'FF0F172A';
    const MID        = 'FF475569';
    const GREEN      = 'FF16A34A';
    const ORANGE     = 'FFD97706';
    const RED        = 'FFDC2626';

    private Spreadsheet $spreadsheet;
    private $sheet;
    private int $row = 1;

    public static function download(SalesInvoice $invoice): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $invoice->load(['party', 'items.product']);
        $org = OrganizationSetting::first();

        $export = new self();
        $export->build($invoice, $org);

        $filename = 'invoice-' . $invoice->invoice_number . '.xlsx';

        return response()->streamDownload(function () use ($export) {
            $writer = new Xlsx($export->spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function build(SalesInvoice $invoice, OrganizationSetting $org): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('Invoice');
        $this->sheet->setShowGridlines(false);

        // Column widths: A=pad, B=item#, C=description, D=sku, E=qty, F=price, G=discount, H=total, I=pad
        foreach (['A' => 3, 'B' => 5, 'C' => 34, 'D' => 16, 'E' => 9, 'F' => 14, 'G' => 14, 'H' => 16, 'I' => 3] as $col => $w) {
            $this->sheet->getColumnDimension($col)->setWidth($w);
        }

        $this->buildBanner($org);
        $this->buildMeta($invoice);
        $this->buildAddresses($invoice, $org);
        $this->buildItemsTable($invoice);
        $this->buildTotals($invoice, $org);
        $this->buildNotes($invoice);
        $this->buildSignatures();
        $this->buildFooter($org);

        $this->sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)->setFitToHeight(0);
    }

    // ── BANNER ────────────────────────────────────────────────────────────────
    private function buildBanner(OrganizationSetting $org): void
    {
        // Top pad
        $this->rowHeight(6);
        $this->fillRow('A', 'I', self::NAVY);
        $this->row++;

        // Company name
        $this->rowHeight(36);
        $this->fillRow('A', 'I', self::NAVY);
        $this->merge("B{$this->row}:H{$this->row}");
        $this->set("B{$this->row}", $org->organization_name)
             ->navy()->bold(18)->white()->valign()->done();
        $this->row++;

        // Org details
        $this->rowHeight(15);
        $this->fillRow('A', 'I', self::NAVY);
        $this->merge("B{$this->row}:H{$this->row}");
        $parts = array_filter([$org->address, $org->phone ? 'T: '.$org->phone : null, $org->email]);
        $this->set("B{$this->row}", implode('   |   ', $parts))
             ->navy()->size(8)->color('FF93C5FD')->valign()->done();
        $this->row++;

        // Bottom pad
        $this->rowHeight(6);
        $this->fillRow('A', 'I', self::NAVY);
        $this->row++;

        // Gap
        $this->rowHeight(10);
        $this->row++;
    }

    // ── INVOICE META (title + number + status + dates) ────────────────────────
    private function buildMeta(SalesInvoice $invoice): void
    {
        $statusColors = [
            'paid'      => [self::GREEN,  'FFD1FAE5'],
            'unpaid'    => [self::ORANGE, 'FFFEF3C7'],
            'partial'   => [self::BLUE,   'FFDBEAFE'],
            'cancelled' => [self::RED,    'FFFEE2E2'],
            'overdue'   => [self::RED,    'FFFEE2E2'],
        ];
        [$sc, $sbg] = $statusColors[strtolower($invoice->status)] ?? [self::MID, self::SILVER];

        // "INVOICE" title
        $this->rowHeight(30);
        $this->merge("B{$this->row}:D{$this->row}");
        $this->set("B{$this->row}", 'INVOICE')->bold(20)->color(self::NAVY)->valign()->done();
        $this->merge("F{$this->row}:H{$this->row}");
        $this->set("F{$this->row}", $invoice->invoice_number)->bold(13)->color(self::BLUE)->halign('right')->valign()->done();
        $this->row++;

        // Status badge + date
        $this->rowHeight(20);
        $this->merge("B{$this->row}:D{$this->row}");
        $this->set("B{$this->row}", '  '.strtoupper($invoice->status).'  ')
             ->bold(8)->color($sc)->bg($sbg)->halign('left')->valign()->done();
        $this->merge("F{$this->row}:H{$this->row}");
        $dateStr = 'Date: '.$invoice->invoice_date->format('d/m/Y');
        if ($invoice->due_date) $dateStr .= '    Due: '.$invoice->due_date->format('d/m/Y');
        $this->set("F{$this->row}", $dateStr)->size(8)->color(self::MID)->halign('right')->valign()->done();
        $this->row++;

        // Divider
        $this->rowHeight(4);
        $this->fillRow('B', 'H', self::BLUE);
        $this->row++;

        $this->rowHeight(8);
        $this->row++;
    }

    // ── ADDRESS BLOCKS ────────────────────────────────────────────────────────
    private function buildAddresses(SalesInvoice $invoice, OrganizationSetting $org): void
    {
        $startRow = $this->row;

        // BILL FROM (B-D)
        $fromRow = $this->addressBlock($startRow, 'B', 'D', 'BILL FROM',
            $org->organization_name, $org->address, $org->phone, $org->email);

        // BILL TO (F-H) — rewind row
        $this->row = $startRow;
        $toRow = $this->addressBlock($startRow, 'F', 'H', 'BILL TO',
            $invoice->party->name,
            $invoice->party->address,
            $invoice->party->phone,
            $invoice->party->email);

        $this->row = max($fromRow, $toRow);

        $this->rowHeight(10);
        $this->row++;
    }

    private function addressBlock(int $startRow, string $startCol, string $endCol, string $label, string $name, ?string $addr, ?string $phone, ?string $email): int
    {
        $r = $startRow;

        // Label header
        $this->sheet->getRowDimension($r)->setRowHeight(15);
        $this->sheet->mergeCells("{$startCol}{$r}:{$endCol}{$r}");
        $this->set("{$startCol}{$r}", $label)->bold(8)->white()->bg(self::BLUE)->halign('left')->valign()->indent(1)->done();
        $r++;

        // Name
        $this->sheet->getRowDimension($r)->setRowHeight(20);
        $this->sheet->mergeCells("{$startCol}{$r}:{$endCol}{$r}");
        $this->set("{$startCol}{$r}", $name)->bold(11)->color(self::DARK)->bg(self::SILVER)->valign()->indent(1)->done();
        $r++;

        // Detail lines
        foreach (array_filter([$addr, $phone ? 'T: '.$phone : null, $email]) as $detail) {
            $this->sheet->getRowDimension($r)->setRowHeight(13);
            $this->sheet->mergeCells("{$startCol}{$r}:{$endCol}{$r}");
            $this->set("{$startCol}{$r}", $detail)->size(8)->color(self::MID)->bg(self::SILVER)->valign()->indent(1)->done();
            $r++;
        }

        return $r;
    }

    // ── ITEMS TABLE ───────────────────────────────────────────────────────────
    private function buildItemsTable(SalesInvoice $invoice): void
    {
        // Header
        $this->rowHeight(20);
        $headers = ['B' => '#', 'C' => 'DESCRIPTION', 'D' => 'SKU / ISBN', 'E' => 'QTY', 'F' => 'UNIT PRICE', 'G' => 'DISCOUNT', 'H' => 'LINE TOTAL'];
        foreach ($headers as $col => $label) {
            $this->set("{$col}{$this->row}", $label)
                 ->bold(8)->white()->bg(self::NAVY)->halign('center')->valign()
                 ->border()->done();
        }
        $this->row++;

        // Items
        foreach ($invoice->items as $i => $item) {
            $this->rowHeight(18);
            $bg = $i % 2 === 0 ? self::WHITE : self::GRAY_ROW;

            $this->set("B{$this->row}", $i + 1)->size(9)->color(self::DARK)->bg($bg)->halign('center')->valign()->border()->done();
            $this->set("C{$this->row}", $item->product_name)->bold(9)->color(self::DARK)->bg($bg)->halign('left')->valign()->indent(1)->border()->done();
            $this->set("D{$this->row}", $item->product_sku ?? '')->size(8)->color(self::MID)->bg($bg)->halign('center')->valign()->border()->done();
            $this->set("E{$this->row}", (int)$item->quantity)->bold(9)->color(self::DARK)->bg($bg)->halign('center')->valign()->border()->format('#,##0')->done();
            $this->set("F{$this->row}", (float)$item->unit_price)->size(9)->color(self::DARK)->bg($bg)->halign('right')->valign()->border()->format('#,##0.00')->done();
            $this->set("G{$this->row}", (float)$item->discount_amount)->size(9)->color(self::MID)->bg($bg)->halign('right')->valign()->border()->format('#,##0.00')->done();
            $this->set("H{$this->row}", (float)$item->line_total)->bold(9)->color(self::DARK)->bg($bg)->halign('right')->valign()->border()->format('#,##0.00')->done();
            $this->row++;
        }

        $this->rowHeight(6);
        $this->row++;
    }

    // ── TOTALS ────────────────────────────────────────────────────────────────
    private function buildTotals(SalesInvoice $invoice, OrganizationSetting $org): void
    {
        $currency = $org->currency_symbol ?? '';
        $fmt = '#,##0.00'.($currency ? "\" {$currency}\"" : '');

        $this->totalLine('Subtotal:', (float)$invoice->subtotal);

        if ($invoice->discount_amount > 0)
            $this->totalLine('Discount:', -(float)$invoice->discount_amount, self::ORANGE);

        if ($invoice->is_taxable && $invoice->tax_amount > 0)
            $this->totalLine("Tax ({$invoice->tax_rate}%):", (float)$invoice->tax_amount);

        // Grand total
        $this->rowHeight(26);
        $this->set("G{$this->row}", 'TOTAL:')->bold(12)->white()->bg(self::NAVY)->halign('right')->valign()->thickBorder()->done();
        $this->set("H{$this->row}", (float)$invoice->total_amount)->bold(13)->white()->bg(self::NAVY)->halign('right')->valign()->thickBorder()->format($fmt)->done();
        $this->row++;

        if ($invoice->paid_amount > 0) {
            $this->totalLine('Amount Paid:', (float)$invoice->paid_amount, self::GREEN);
        }

        if ($invoice->outstanding_balance > 0) {
            $this->rowHeight(22);
            $this->set("G{$this->row}", 'BALANCE DUE:')->bold(10)->color(self::RED)->bg(self::SILVER)->halign('right')->valign()->border()->done();
            $this->set("H{$this->row}", (float)$invoice->outstanding_balance)->bold(12)->color(self::RED)->bg(self::SILVER)->halign('right')->valign()->border()->format($fmt)->done();
            $this->row++;
        }

        $this->rowHeight(12);
        $this->row++;
    }

    private function totalLine(string $label, float $value, string $color = null): void
    {
        $this->rowHeight(15);
        $this->set("G{$this->row}", $label)->size(9)->color(self::MID)->halign('right')->valign()->border()->done();
        $b = $this->set("H{$this->row}", $value)->size(9)->halign('right')->valign()->border()->format('#,##0.00');
        if ($color) $b->color($color);
        $b->done();
        $this->row++;
    }

    // ── NOTES ─────────────────────────────────────────────────────────────────
    private function buildNotes(SalesInvoice $invoice): void
    {
        foreach ([['Notes', $invoice->notes], ['Payment Terms', $invoice->payment_terms]] as [$label, $content]) {
            if (!$content) continue;

            $this->rowHeight(14);
            $this->merge("B{$this->row}:H{$this->row}");
            $this->set("B{$this->row}", strtoupper($label))->bold(8)->white()->bg(self::BLUE)->halign('left')->valign()->indent(1)->done();
            $this->row++;

            $this->rowHeight(14);
            $this->merge("B{$this->row}:H{$this->row}");
            $this->set("B{$this->row}", $content)->size(9)->color(self::MID)->bg(self::SILVER)->halign('left')->valign()->wrap()->indent(1)->done();
            $this->row++;
        }

        $this->rowHeight(8);
        $this->row++;
    }

    // ── SIGNATURES ────────────────────────────────────────────────────────────
    private function buildSignatures(): void
    {
        $this->rowHeight(36);
        $this->row++;

        // Signature lines
        $this->rowHeight(4);
        foreach (['B', 'C', 'D'] as $c) {
            $this->sheet->getStyle("{$c}{$this->row}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setARGB(self::NAVY);
        }
        foreach (['F', 'G', 'H'] as $c) {
            $this->sheet->getStyle("{$c}{$this->row}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->getColor()->setARGB(self::NAVY);
        }
        $this->row++;

        $this->rowHeight(14);
        $this->merge("B{$this->row}:D{$this->row}");
        $this->set("B{$this->row}", 'Accountant')->bold(9)->color(self::DARK)->halign('center')->done();
        $this->merge("F{$this->row}:H{$this->row}");
        $this->set("F{$this->row}", 'Customer')->bold(9)->color(self::DARK)->halign('center')->done();
        $this->row++;
    }

    // ── FOOTER ────────────────────────────────────────────────────────────────
    private function buildFooter(OrganizationSetting $org): void
    {
        $this->rowHeight(8);
        $this->row++;

        $this->rowHeight(18);
        $this->fillRow('A', 'I', self::NAVY);
        $this->merge("B{$this->row}:H{$this->row}");
        $this->set("B{$this->row}", 'Thank you for your business!   •   '.$org->organization_name)
             ->navy()->size(9)->color('FF93C5FD')->halign('center')->valign()->italic()->done();
        $this->row++;

        $this->rowHeight(5);
        $this->fillRow('A', 'I', self::NAVY);
    }

    // ── FLUENT CELL BUILDER ───────────────────────────────────────────────────
    private function set(string $coord, $value): CellBuilder
    {
        $this->sheet->getCell($coord)->setValue($value);
        return new CellBuilder($this->sheet, $coord);
    }

    private function merge(string $range): void
    {
        $this->sheet->mergeCells($range);
    }

    private function rowHeight(int $height): void
    {
        $this->sheet->getRowDimension($this->row)->setRowHeight($height);
    }

    private function fillRow(string $fromCol, string $toCol, string $argb): void
    {
        $this->sheet->getStyle("{$fromCol}{$this->row}:{$toCol}{$this->row}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($argb);
    }
}


/**
 * Fluent helper for styling a single cell
 */
class CellBuilder
{
    private $sheet;
    private string $coord;
    private array $fontOpts   = ['name' => 'Arial'];
    private ?string $fillArgb = null;
    private array $alignOpts  = [];
    private bool $hasBorder   = false;
    private bool $hasThickBorder = false;
    private ?string $numFormat = null;

    public function __construct($sheet, string $coord)
    {
        $this->sheet = $sheet;
        $this->coord = $coord;
    }

    public function bold(int $size = 9):   self { $this->fontOpts['bold'] = true; $this->fontOpts['size'] = $size; return $this; }
    public function size(int $size):       self { $this->fontOpts['size'] = $size; return $this; }
    public function italic():              self { $this->fontOpts['italic'] = true; return $this; }
    public function white():               self { $this->fontOpts['color'] = ExportSalesInvoice::WHITE; return $this; }
    public function color(string $argb):   self { $this->fontOpts['color'] = $argb; return $this; }
    public function navy():                self { $this->fillArgb = ExportSalesInvoice::NAVY; return $this; }
    public function bg(string $argb):      self { $this->fillArgb = $argb; return $this; }
    public function halign(string $h):     self { $this->alignOpts['h'] = $h; return $this; }
    public function valign(string $v = 'center'): self { $this->alignOpts['v'] = $v; return $this; }
    public function indent(int $n = 1):    self { $this->alignOpts['indent'] = $n; return $this; }
    public function wrap():                self { $this->alignOpts['wrap'] = true; return $this; }
    public function border():              self { $this->hasBorder = true; return $this; }
    public function thickBorder():         self { $this->hasThickBorder = true; return $this; }
    public function format(string $fmt):   self { $this->numFormat = $fmt; return $this; }

    public function done(): void
    {
        $style = $this->sheet->getStyle($this->coord);

        // Font
        $font = $style->getFont()->setName($this->fontOpts['name'] ?? 'Arial');
        if (!empty($this->fontOpts['bold']))   $font->setBold(true);
        if (!empty($this->fontOpts['italic'])) $font->setItalic(true);
        if (!empty($this->fontOpts['size']))   $font->setSize($this->fontOpts['size']);
        if (!empty($this->fontOpts['color']))  $font->getColor()->setARGB($this->fontOpts['color']);

        // Fill
        if ($this->fillArgb) {
            $style->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($this->fillArgb);
        }

        // Alignment
        $align = $style->getAlignment();
        if (!empty($this->alignOpts['h']))      $align->setHorizontal($this->alignOpts['h']);
        if (!empty($this->alignOpts['v']))      $align->setVertical($this->alignOpts['v']);
        if (!empty($this->alignOpts['wrap']))   $align->setWrapText(true);
        if (!empty($this->alignOpts['indent'])) $align->setIndent($this->alignOpts['indent']);

        // Border
        if ($this->hasThickBorder) {
            $style->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => ExportSalesInvoice::NAVY]]]]);
        } elseif ($this->hasBorder) {
            $style->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]]]);
        }

        // Number format
        if ($this->numFormat) {
            $style->getNumberFormat()->setFormatCode($this->numFormat);
        }
    }
}