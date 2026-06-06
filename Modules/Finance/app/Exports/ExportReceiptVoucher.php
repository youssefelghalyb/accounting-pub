<?php

namespace Modules\Finance\Exports;

use Modules\Finance\Models\ReceiptVoucher;
use Modules\Settings\Models\OrganizationSetting;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ExportReceiptVoucher
{
    const NAVY    = 'FF1E3A5F';
    const BLUE    = 'FF2563EB';
    const GREEN   = 'FF059669';   // emerald — matches the receipt show page banner
    const SILVER  = 'FFF1F5F9';
    const GRAY_R  = 'FFF8FAFC';
    const WHITE   = 'FFFFFFFF';
    const DARK    = 'FF0F172A';
    const MID     = 'FF475569';
    const RED     = 'FFDC2626';
    const ORANGE  = 'FFD97706';

    private Spreadsheet $spreadsheet;
    private $sheet;
    private int $row = 1;

    // ── Entry point ──────────────────────────────────────────────────────────
    public static function download(ReceiptVoucher $voucher): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $voucher->load(['party', 'account', 'salesInvoice']);
        $org = OrganizationSetting::first();

        $export = new self();
        $export->build($voucher, $org);

        $filename = 'receipt-' . $voucher->voucher_number . '.xlsx';

        return response()->streamDownload(function () use ($export) {
            (new Xlsx($export->spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // ── Build workbook ───────────────────────────────────────────────────────
    private function build(ReceiptVoucher $voucher, OrganizationSetting $org): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle('Receipt Voucher');
        $this->sheet->setShowGridlines(false);

        // A=pad, B=label, C-E=value / wide content, F=label, G-H=value, I=pad
        foreach (['A' => 3, 'B' => 22, 'C' => 14, 'D' => 14, 'E' => 14, 'F' => 22, 'G' => 14, 'H' => 14, 'I' => 3] as $col => $w) {
            $this->sheet->getColumnDimension($col)->setWidth($w);
        }

        $this->buildBanner($org);
        $this->buildMeta($voucher);
        $this->buildAmountBanner($voucher, $org);
        $this->buildPartyAccount($voucher);
        $this->buildLinkedInvoice($voucher, $org);
        $this->buildNotes($voucher);
        $this->buildSignatures();
        $this->buildFooter($org);

        $this->sheet->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)->setFitToHeight(0);
    }

    // ── Banner ───────────────────────────────────────────────────────────────
    private function buildBanner(OrganizationSetting $org): void
    {
        $this->rowH(6); $this->fillRow('A', 'I', self::NAVY); $this->row++;

        $this->rowH(36); $this->fillRow('A', 'I', self::NAVY);
        $this->merge("B{$this->row}:H{$this->row}");
        $this->cell("B{$this->row}", $org->organization_name)->bold(20)->white()->valign()->done();
        $this->row++;

        $this->rowH(15); $this->fillRow('A', 'I', self::NAVY);
        $this->merge("B{$this->row}:H{$this->row}");
        $parts = array_filter([$org->address, $org->phone ? 'T: '.$org->phone : null, $org->email]);
        $this->cell("B{$this->row}", implode('   |   ', $parts))->size(8)->color('FF93C5FD')->valign()->done();
        $this->row++;

        $this->rowH(6); $this->fillRow('A', 'I', self::NAVY); $this->row++;
        $this->rowH(10); $this->row++;
    }

    // ── Voucher meta (number, date, method) ──────────────────────────────────
    private function buildMeta(ReceiptVoucher $voucher): void
    {
        // Title left / voucher number right
        $this->rowH(30);
        $this->merge("B{$this->row}:D{$this->row}");
        $this->cell("B{$this->row}", 'RECEIPT VOUCHER')->bold(18)->color(self::NAVY)->valign()->done();
        $this->merge("F{$this->row}:H{$this->row}");
        $this->cell("F{$this->row}", $voucher->voucher_number)->bold(13)->color(self::GREEN)->halign('right')->valign()->done();
        $this->row++;

        // Date / payment method
        $this->rowH(20);
        $this->merge("B{$this->row}:D{$this->row}");
        $this->cell("B{$this->row}", 'Date: '.$voucher->voucher_date->format('d/m/Y'))->size(9)->color(self::MID)->valign()->done();
        $this->merge("F{$this->row}:H{$this->row}");
        $method = '  '.strtoupper($voucher->payment_method_label).'  ';
        $this->cell("F{$this->row}", $method)->bold(8)->color(self::GREEN)->bg('FFD1FAE5')->halign('right')->valign()->done();
        $this->row++;

        if ($voucher->reference_number) {
            $this->rowH(16);
            $this->merge("B{$this->row}:D{$this->row}");
            $this->cell("B{$this->row}", 'Ref: '.$voucher->reference_number)->size(8)->color(self::MID)->valign()->done();
            $this->row++;
        }

        // Accent divider
        $this->rowH(4); $this->fillRow('B', 'H', self::GREEN); $this->row++;
        $this->rowH(8); $this->row++;
    }

    // ── Big amount banner ─────────────────────────────────────────────────────
    private function buildAmountBanner(ReceiptVoucher $voucher, OrganizationSetting $org): void
    {
        $currency = $org->currency_symbol ?? '';

        $this->rowH(14);
        $this->fillRow('B', 'H', self::GREEN);
        $this->merge("B{$this->row}:H{$this->row}");
        $this->cell("B{$this->row}", 'AMOUNT RECEIVED')->bold(9)->white()->halign('center')->valign()->done();
        $this->row++;

        $this->rowH(40);
        $this->fillRow('B', 'H', self::GREEN);
        $this->merge("B{$this->row}:H{$this->row}");
        $amountStr = number_format((float)$voucher->amount, 2).($currency ? ' '.$currency : '');
        $this->cell("B{$this->row}", (float)$voucher->amount)
            ->bold(24)->white()->halign('center')->valign()
            ->format('#,##0.00'.($currency ? "\" {$currency}\"" : ''))
            ->done();
        $this->row++;

        $this->rowH(4); $this->fillRow('B', 'H', self::NAVY); $this->row++;
        $this->rowH(10); $this->row++;
    }

    // ── Party + Account side by side ──────────────────────────────────────────
    private function buildPartyAccount(ReceiptVoucher $voucher): void
    {
        $startRow = $this->row;
        $fromEnd = $this->addressBlock($startRow, 'B', 'D', 'RECEIVED FROM',
            $voucher->party->name,
            $voucher->party->address,
            $voucher->party->phone,
            $voucher->party->email
        );

        $this->row = $startRow;
        $toEnd = $this->addressBlock($startRow, 'F', 'H', 'DEPOSITED TO',
            $voucher->account->account_name,
            $voucher->account->account_number ? 'Account No: '.$voucher->account->account_number : null,
            $voucher->account->bank_name,
            null
        );

        $this->row = max($fromEnd, $toEnd);
        $this->rowH(10); $this->row++;
    }

    private function addressBlock(int $startRow, string $sc, string $ec, string $label, string $name, ?string $line1, ?string $line2, ?string $line3): int
    {
        $r = $startRow;

        $this->sheet->getRowDimension($r)->setRowHeight(15);
        $this->sheet->mergeCells("{$sc}{$r}:{$ec}{$r}");
        $this->cell("{$sc}{$r}", $label)->bold(8)->white()->bg(self::GREEN)->halign('left')->valign()->indent(1)->done();
        $r++;

        $this->sheet->getRowDimension($r)->setRowHeight(20);
        $this->sheet->mergeCells("{$sc}{$r}:{$ec}{$r}");
        $this->cell("{$sc}{$r}", $name)->bold(11)->color(self::DARK)->bg(self::SILVER)->valign()->indent(1)->done();
        $r++;

        foreach (array_filter([$line1, $line2, $line3]) as $line) {
            $this->sheet->getRowDimension($r)->setRowHeight(13);
            $this->sheet->mergeCells("{$sc}{$r}:{$ec}{$r}");
            $this->cell("{$sc}{$r}", $line)->size(8)->color(self::MID)->bg(self::SILVER)->valign()->indent(1)->done();
            $r++;
        }

        return $r;
    }

    // ── Linked invoice table ──────────────────────────────────────────────────
    private function buildLinkedInvoice(ReceiptVoucher $voucher, OrganizationSetting $org): void
    {
        if (!$voucher->salesInvoice) return;

        $inv = $voucher->salesInvoice;
        $currency = $org->currency_symbol ?? '';
        $fmt = '#,##0.00'.($currency ? "\" {$currency}\"" : '');

        // Section label
        $this->rowH(14);
        $this->merge("B{$this->row}:H{$this->row}");
        $this->cell("B{$this->row}", 'LINKED INVOICE')->bold(8)->white()->bg(self::NAVY)->halign('left')->valign()->indent(1)->done();
        $this->row++;

        // Table header
        $this->rowH(18);
        $headers = ['B' => 'Invoice No.', 'D' => 'Date', 'E' => 'Total', 'F' => 'Paid', 'G' => 'Status', 'H' => 'Balance Due'];
        foreach ($headers as $col => $lbl) {
            $this->cell("{$col}{$this->row}", $lbl)->bold(8)->white()->bg(self::NAVY)->halign('center')->valign()->border()->done();
        }
        // Fill gaps between merged header cols
        foreach (['C'] as $col) {
            $this->sheet->getStyle("{$col}{$this->row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::NAVY);
        }
        $this->row++;

        // Data row
        $this->rowH(18);
        $this->cell("B{$this->row}", $inv->invoice_number)->bold(9)->color(self::DARK)->bg(self::SILVER)->halign('left')->valign()->indent(1)->border()->done();
        $this->sheet->mergeCells("B{$this->row}:C{$this->row}");
        $this->cell("D{$this->row}", $inv->invoice_date->format('d/m/Y'))->size(9)->color(self::DARK)->bg(self::SILVER)->halign('center')->valign()->border()->done();
        $this->cell("E{$this->row}", (float)$inv->total_amount)->size(9)->color(self::DARK)->bg(self::SILVER)->halign('right')->valign()->border()->format($fmt)->done();
        $this->cell("F{$this->row}", (float)$inv->paid_amount)->size(9)->color(self::GREEN)->bg(self::SILVER)->halign('right')->valign()->border()->format($fmt)->done();
        $this->cell("G{$this->row}", strtoupper($inv->status))->size(8)->color(self::NAVY)->bg(self::SILVER)->halign('center')->valign()->border()->done();
        $this->cell("H{$this->row}", (float)$inv->outstanding_balance)->bold(9)->color(self::RED)->bg(self::SILVER)->halign('right')->valign()->border()->format($fmt)->done();
        $this->row++;

        $this->rowH(10); $this->row++;
    }

    // ── Notes / description ───────────────────────────────────────────────────
    private function buildNotes(ReceiptVoucher $voucher): void
    {
        foreach ([['Description', $voucher->description], ['Notes', $voucher->notes]] as [$label, $content]) {
            if (!$content) continue;

            $this->rowH(14);
            $this->merge("B{$this->row}:H{$this->row}");
            $this->cell("B{$this->row}", strtoupper($label))->bold(8)->white()->bg(self::BLUE)->halign('left')->valign()->indent(1)->done();
            $this->row++;

            $this->rowH(14);
            $this->merge("B{$this->row}:H{$this->row}");
            $this->cell("B{$this->row}", $content)->size(9)->color(self::MID)->bg(self::SILVER)->halign('left')->valign()->wrap()->indent(1)->done();
            $this->row++;
        }

        $this->rowH(8); $this->row++;
    }

    // ── Signatures (3 columns: Accountant / Finance Manager / Received By) ────
    private function buildSignatures(): void
    {
        $this->rowH(36); $this->row++;

        // Signature lines
        $this->rowH(4);
        foreach (['B', 'C'] as $c) {
            $this->sheet->getStyle("{$c}{$this->row}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB(self::NAVY);
        }
        foreach (['D', 'E'] as $c) {
            $this->sheet->getStyle("{$c}{$this->row}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB(self::NAVY);
        }
        foreach (['G', 'H'] as $c) {
            $this->sheet->getStyle("{$c}{$this->row}")->getBorders()->getBottom()
                ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB(self::NAVY);
        }
        $this->row++;

        $this->rowH(14);
        $this->merge("B{$this->row}:C{$this->row}");
        $this->cell("B{$this->row}", 'Accountant')->bold(9)->color(self::DARK)->halign('center')->done();
        $this->merge("D{$this->row}:E{$this->row}");
        $this->cell("D{$this->row}", 'Finance Manager')->bold(9)->color(self::DARK)->halign('center')->done();
        $this->merge("G{$this->row}:H{$this->row}");
        $this->cell("G{$this->row}", 'Received By')->bold(9)->color(self::DARK)->halign('center')->done();
        $this->row++;
    }

    // ── Footer ────────────────────────────────────────────────────────────────
    private function buildFooter(OrganizationSetting $org): void
    {
        $this->rowH(8); $this->row++;

        $this->rowH(18);
        $this->fillRow('A', 'I', self::NAVY);
        $this->merge("B{$this->row}:H{$this->row}");
        $this->cell("B{$this->row}", 'Thank you for your payment!   •   '.$org->organization_name)
            ->size(9)->color('FF93C5FD')->italic()->halign('center')->valign()->done();
        $this->row++;

        $this->rowH(5); $this->fillRow('A', 'I', self::NAVY);
    }

    // ── Fluent cell builder ───────────────────────────────────────────────────
    private function cell(string $coord, $value): CellBuilderRV
    {
        $this->sheet->getCell($coord)->setValue($value);
        return new CellBuilderRV($this->sheet, $coord);
    }

    private function merge(string $range): void { $this->sheet->mergeCells($range); }

    private function rowH(int $h): void { $this->sheet->getRowDimension($this->row)->setRowHeight($h); }

    private function fillRow(string $from, string $to, string $argb): void
    {
        $this->sheet->getStyle("{$from}{$this->row}:{$to}{$this->row}")
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($argb);
    }
}


/**
 * Fluent cell styler (scoped to receipt export to avoid class collision)
 */
class CellBuilderRV
{
    private $sheet;
    private string $coord;
    private array $f    = ['name' => 'Arial'];
    private ?string $bg = null;
    private array $a    = [];
    private bool $border = false;
    private ?string $fmt = null;

    public function __construct($sheet, string $coord)
    {
        $this->sheet = $sheet;
        $this->coord = $coord;
    }

    public function bold(int $size = 9): self { $this->f['bold'] = true; $this->f['size'] = $size; return $this; }
    public function size(int $s): self         { $this->f['size'] = $s; return $this; }
    public function italic(): self             { $this->f['italic'] = true; return $this; }
    public function white(): self              { $this->f['color'] = ExportReceiptVoucher::WHITE; return $this; }
    public function color(string $a): self     { $this->f['color'] = $a; return $this; }
    public function bg(string $a): self        { $this->bg = $a; return $this; }
    public function halign(string $h): self    { $this->a['h'] = $h; return $this; }
    public function valign(string $v = 'center'): self { $this->a['v'] = $v; return $this; }
    public function indent(int $n = 1): self   { $this->a['indent'] = $n; return $this; }
    public function wrap(): self               { $this->a['wrap'] = true; return $this; }
    public function border(): self             { $this->border = true; return $this; }
    public function format(string $f): self    { $this->fmt = $f; return $this; }

    public function done(): void
    {
        $style = $this->sheet->getStyle($this->coord);

        $font = $style->getFont()->setName($this->f['name'] ?? 'Arial');
        if (!empty($this->f['bold']))   $font->setBold(true);
        if (!empty($this->f['italic'])) $font->setItalic(true);
        if (!empty($this->f['size']))   $font->setSize($this->f['size']);
        if (!empty($this->f['color']))  $font->getColor()->setARGB($this->f['color']);

        if ($this->bg) {
            $style->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($this->bg);
        }

        $align = $style->getAlignment();
        if (!empty($this->a['h']))      $align->setHorizontal($this->a['h']);
        if (!empty($this->a['v']))      $align->setVertical($this->a['v']);
        if (!empty($this->a['wrap']))   $align->setWrapText(true);
        if (!empty($this->a['indent'])) $align->setIndent($this->a['indent']);

        if ($this->border) {
            $style->applyFromArray(['borders' => ['allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => 'FFCBD5E1'],
            ]]]);
        }

        if ($this->fmt) {
            $style->getNumberFormat()->setFormatCode($this->fmt);
        }
    }
}