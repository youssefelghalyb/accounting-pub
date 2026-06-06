<?php

namespace Modules\Product\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
 
class BooksExport
{
    public function download(string $filename = null): StreamedResponse
    {
        $filename ??= 'books_' . now()->format('Y-m-d_His') . '.xlsx';
 
        $spreadsheet = $this->build();
        $writer      = new Xlsx($spreadsheet);
 
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
 
    // -------------------------------------------------------------------------
 
    private function build(): Spreadsheet
    {
        [$books, $maxAuthors] = $this->fetchBooks();
 
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Books');
 
        $headings = $this->headings($maxAuthors);
 
        $this->writeHeadings($sheet, $headings);
        $this->writeRows($sheet, $books, $maxAuthors);
        $this->applyStyles($sheet, count($books), count($headings));
 
        return $spreadsheet;
    }
 
    // -------------------------------------------------------------------------
 
    private function fetchBooks(): array
    {
        $books = DB::table('books')
            ->join('products', 'products.id', '=', 'books.product_id')
            ->leftJoin('book_categories as cat', 'cat.id', '=', 'books.category_id')
            ->leftJoin('book_categories as subcat', 'subcat.id', '=', 'books.sub_category_id')
            ->select(
                'books.id as book_id',
                'books.product_id',
                'products.name as book_name',
                'products.sku',
                'products.base_price',
                'products.status',
                'products.description',
                'books.isbn',
                'books.num_of_pages',
                'books.cover_type',
                'books.published_at',
                'books.language',
                'books.is_translated',
                'books.translated_from',
                'books.translated_to',
                'books.translator_name',
                'cat.name as category',
                'subcat.name as sub_category',
                DB::raw('EXISTS (
                    SELECT 1 FROM sales_invoice_items
                    WHERE sales_invoice_items.product_id = products.id
                ) as used_in_sales'),
            )
            ->get();
 
        // Bulk-fetch all authors for all books in one query
        $bookIds = $books->pluck('book_id')->toArray();
 
        $authorsByBook = DB::table('contract_authors')
            ->join('author_book_contracts', 'author_book_contracts.id', '=', 'contract_authors.contract_id')
            ->join('authors', 'authors.id', '=', 'contract_authors.author_id')
            ->whereIn('author_book_contracts.book_id', $bookIds)
            ->orderByDesc('contract_authors.is_representative') // representative first
            ->orderBy('contract_authors.id')
            ->select('author_book_contracts.book_id', 'authors.full_name')
            ->get()
            ->groupBy('book_id')
            ->map(fn($rows) => $rows->pluck('full_name')->unique()->values());
 
        $maxAuthors = 1;
 
        $books = $books->map(function ($book) use ($authorsByBook, &$maxAuthors) {
            $book->authors = $authorsByBook[$book->book_id] ?? collect();
 
            if ($book->authors->count() > $maxAuthors) {
                $maxAuthors = $book->authors->count();
            }
 
            return $book;
        });
 
        return [$books, $maxAuthors];
    }
 
    // -------------------------------------------------------------------------
 
    private function headings(int $maxAuthors): array
    {
        $base = [
            'Book ID',
            'Product ID',
            'Book Name',
            'SKU',
            'Price',
            'Status',
            'Description',
            'ISBN',
            'Pages',
            'Cover Type',
            'Published Date',
            'Language',
            'Is Translated',
            'Translated From',
            'Translated To',
            'Translator',
            'Category',
            'Sub Category',
            'Used in Sales',
        ];
 
        for ($i = 1; $i <= $maxAuthors; $i++) {
            $base[] = "Author $i";
        }
 
        return $base;
    }
 
    private function writeHeadings(Worksheet $sheet, array $headings): void
    {
        foreach ($headings as $colIndex => $heading) {
            $col = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue("{$col}1", $heading);
        }
    }
 
    private function writeRows(Worksheet $sheet, $books, int $maxAuthors): void
    {
        foreach ($books as $rowIndex => $book) {
            $excelRow = $rowIndex + 2;
 
            $row = [
                $book->book_id,
                $book->product_id,
                $book->book_name,
                $book->sku,
                (float) $book->base_price,
                ucfirst($book->status),
                $book->description,
                $book->isbn,
                $book->num_of_pages,
                ucfirst($book->cover_type),
                $book->published_at,
                $book->language,
                $book->is_translated ? 'Yes' : 'No',
                $book->is_translated ? ($book->translated_from ?? '') : '',
                $book->is_translated ? ($book->translated_to   ?? '') : '',
                $book->is_translated ? ($book->translator_name ?? '') : '',
                $book->category     ?? '',
                $book->sub_category ?? '',
                $book->used_in_sales ? 'Yes' : 'No',
            ];
 
            for ($i = 0; $i < $maxAuthors; $i++) {
                $row[] = $book->authors[$i] ?? '';
            }
 
            foreach ($row as $colIndex => $value) {
                $col = Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue("{$col}{$excelRow}", $value);
            }
        }
    }
 
    // -------------------------------------------------------------------------
 
    private function applyStyles(Worksheet $sheet, int $rowCount, int $colCount): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $lastRow = $rowCount + 1;
 
        // Header row
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 11,
                'name'  => 'Arial',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1F3864'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFAAAAAA'],
                ],
            ],
        ]);
 
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->freezePane('A2');
 
        // Data rows — zebra striping
        for ($row = 2; $row <= $lastRow; $row++) {
            $bg = ($row % 2 === 0) ? 'FFE9EEF6' : 'FFFFFFFF';
 
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'font' => ['name' => 'Arial', 'size' => 10],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $bg],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFDDDDDD'],
                    ],
                ],
            ]);
        }
 
        // Price column (E = 5) — number format
        if ($rowCount > 0) {
            $sheet->getStyle("E2:E{$lastRow}")
                  ->getNumberFormat()
                  ->setFormatCode('#,##0.00');
        }
 
        // Auto-size all columns
        for ($col = 1; $col <= $colCount; $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }
    }
}