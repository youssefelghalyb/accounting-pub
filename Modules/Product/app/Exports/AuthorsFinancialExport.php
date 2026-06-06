<?php

namespace Modules\Product\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment, Border};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuthorsFinancialExport
{
    private const HDR_BG   = 'FF1F3864';
    private const HDR_FG   = 'FFFFFFFF';
    private const ROW_EVEN = 'FFE9EEF6';
    private const ROW_ODD  = 'FFFFFFFF';

    // -------------------------------------------------------------------------

    public function download(string $filename = null): StreamedResponse
    {
        $filename ??= 'authors_financial_' . now()->format('Y-m-d_His') . '.xlsx';

        $spreadsheet = $this->build();
        $writer      = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn () => $writer->save('php://output'),
            $filename,
            [
                'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    // -------------------------------------------------------------------------

    private function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->buildPaymentVouchersSheet($spreadsheet);
        $this->buildSalesInvoicesSheet($spreadsheet);

        return $spreadsheet;
    }

    // =========================================================================
    // SHEET 1 — Authors with Payment Vouchers
    // =========================================================================

    private function buildPaymentVouchersSheet(Spreadsheet $spreadsheet): void
    {
        $ws = $spreadsheet->createSheet(0);
        $ws->setTitle('Payment Vouchers');

        $rows = DB::table('authors as a')
            ->join('payment_vouchers as pv', 'pv.party_id', '=', 'a.party_id')
            ->whereNotNull('a.party_id')
            ->whereNull('pv.deleted_at')
            ->select(
                'a.full_name        as author_name',
                'pv.voucher_number',
                'pv.voucher_date',
                'pv.amount',
                'pv.payment_method',
                'pv.cheque_number',
                'pv.cheque_date',
                'pv.transaction_reference',
                'pv.description',
            )
            ->orderBy('a.full_name')
            ->orderBy('pv.voucher_date')
            ->get();

        $headers = [
            'Author Name',
            'Voucher Number',
            'Voucher Date',
            'Amount',
            'Payment Method',
            'Cheque Number',
            'Cheque Date',
            'Transaction Ref',
            'Description',
        ];

        $this->writeHeaders($ws, $headers);

        $rowIdx = 2;
        foreach ($rows as $row) {
            $this->writeRow($ws, $rowIdx++, [
                $row->author_name,
                $row->voucher_number,
                $row->voucher_date,
                (float) $row->amount,
                $row->payment_method,
                $row->cheque_number,
                $row->cheque_date,
                $row->transaction_reference,
                $row->description,
            ]);
        }

        if ($rowIdx > 2) {
            $last = $rowIdx - 1;
            $ws->getStyle("C2:C{$last}")->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            $ws->getStyle("G2:G{$last}")->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            $ws->getStyle("D2:D{$last}")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $this->applyStyles($ws, count($headers), $rowIdx - 1);
        $this->autoSize($ws, count($headers));
    }

    // =========================================================================
    // SHEET 2 — Authors as Party on Sales Invoices (with items)
    // =========================================================================

    private function buildSalesInvoicesSheet(Spreadsheet $spreadsheet): void
    {
        $ws = $spreadsheet->createSheet(1);
        $ws->setTitle('Sales Invoices');

        // Authors who ARE the party_id on a sales invoice
        $rows = DB::table('authors as a')
            ->join('sales_invoices as si', function ($join) {
                $join->on('si.party_id', '=', 'a.party_id')
                     ->whereNull('si.deleted_at');
            })
            ->join('sales_invoice_items as sii', 'sii.sales_invoice_id', '=', 'si.id')
            ->whereNotNull('a.party_id')
            ->select(
                'a.full_name        as author_name',
                'si.invoice_number',
                'si.invoice_date',
                'si.status          as invoice_status',
                'si.total_amount    as invoice_total',
                'si.discount_amount as invoice_discount',
                'sii.product_name   as item_name',
                'sii.product_sku    as item_sku',
                'sii.quantity',
                'sii.unit_price',
                'sii.discount_amount as item_discount',
                'sii.line_total',
            )
            ->orderBy('a.full_name')
            ->orderBy('si.invoice_date')
            ->orderBy('sii.id')
            ->get();

        $headers = [
            'Author Name',
            'Invoice Number',
            'Invoice Date',
            'Status',
            'Invoice Total',
            'Invoice Discount',
            'Item Name',
            'Item SKU',
            'Quantity',
            'Unit Price',
            'Item Discount',
            'Line Total',
        ];

        $this->writeHeaders($ws, $headers);

        $rowIdx = 2;
        foreach ($rows as $row) {
            $this->writeRow($ws, $rowIdx++, [
                $row->author_name,
                $row->invoice_number,
                $row->invoice_date,
                ucfirst($row->invoice_status),
                (float) $row->invoice_total,
                (float) $row->invoice_discount,
                $row->item_name,
                $row->item_sku,
                (int)   $row->quantity,
                (float) $row->unit_price,
                (float) $row->item_discount,
                (float) $row->line_total,
            ]);
        }

        if ($rowIdx > 2) {
            $last = $rowIdx - 1;
            $ws->getStyle("C2:C{$last}")->getNumberFormat()->setFormatCode('YYYY-MM-DD');
            foreach (['E', 'F', 'J', 'K', 'L'] as $col) {
                $ws->getStyle("{$col}2:{$col}{$last}")->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        $this->applyStyles($ws, count($headers), $rowIdx - 1);
        $this->autoSize($ws, count($headers));
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function writeHeaders(Worksheet $ws, array $headers): void
    {
        foreach ($headers as $i => $label) {
            $ws->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . '1', $label);
        }
    }

    private function writeRow(Worksheet $ws, int $row, array $values): void
    {
        foreach ($values as $i => $value) {
            $ws->setCellValue(Coordinate::stringFromColumnIndex($i + 1) . $row, $value ?? '');
        }
    }

    private function applyStyles(Worksheet $ws, int $colCount, int $lastRow): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($colCount);

        $ws->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::HDR_FG], 'size' => 11, 'name' => 'Arial'],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::HDR_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);

        $ws->getRowDimension(1)->setRowHeight(28);
        $ws->freezePane('A2');

        for ($r = 2; $r <= $lastRow; $r++) {
            $bg = ($r % 2 === 0) ? self::ROW_EVEN : self::ROW_ODD;
            $ws->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'font'      => ['name' => 'Arial', 'size' => 10],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
            ]);
        }
    }

    private function autoSize(Worksheet $ws, int $colCount): void
    {
        for ($i = 1; $i <= $colCount; $i++) {
            $ws->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
    }
}