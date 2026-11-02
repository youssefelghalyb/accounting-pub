<?php

namespace Modules\Product\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reads the master books sheet (header-name based, column order does not matter) into
 * normalised row arrays. Shared by SyncBooksCommand and PruneBooksCommand so both always agree
 * on which Book ID / Product ID pairs the sheet contains.
 *
 * Headers: Book ID, Product ID, Book Name, SKU, Price, Status, Description, ISBN, Pages,
 * Cover Type, Published Date, Language, Is Translated, Translated From, Translated To,
 * Translator, اشراف, تقديم, Category, Sub Category, Used in Sales, Author 1, المتعاقد,
 * Nationality (or الجنسية), Notes, status2, نسخ الهدايا, رقم الإيداع, سنة الإيداع, حالة الإيداع.
 */
class BooksSheetReader
{
    private const REQUIRED = ['Book ID', 'Product ID', 'Book Name', 'Price', 'ISBN'];

    /** @return array{rows: array<int,array>, errors: array<int,string>} */
    public function read(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();

        $headers = [];
        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $h = trim((string) $cell->getValue());
                if ($h !== '') {
                    $headers[$h] = $cell->getColumn();
                }
            }
        }

        $errors = [];
        foreach (self::REQUIRED as $col) {
            if (! isset($headers[$col])) {
                $errors[] = "Missing required column: {$col}";
            }
        }
        if ($errors) {
            return ['rows' => [], 'errors' => $errors];
        }

        $get = function (array|string $names, int $r) use ($sheet, $headers): ?string {
            foreach ((array) $names as $name) {
                if (isset($headers[$name])) {
                    return $this->cell($sheet, $headers[$name] . $r);
                }
            }

            return null;
        };

        $rows = [];
        $seenBook = $seenProduct = [];

        for ($r = 2, $max = $sheet->getHighestDataRow(); $r <= $max; $r++) {
            $bookId = (int) $get('Book ID', $r);
            $productId = (int) $get('Product ID', $r);
            if (! $bookId && ! $productId && $get('Book Name', $r) === null) {
                continue; // blank line
            }
            if (! $bookId || ! $productId) {
                $errors[] = "Row {$r}: Book ID / Product ID missing";
                continue;
            }
            if (isset($seenBook[$bookId]) || isset($seenProduct[$productId])) {
                $errors[] = "Row {$r}: duplicate Book ID {$bookId} or Product ID {$productId} (first seen row "
                    . ($seenBook[$bookId] ?? $seenProduct[$productId]) . ')';
                continue;
            }
            $seenBook[$bookId] = $seenProduct[$productId] = $r;

            $isTranslated = strtolower($get('Is Translated', $r) ?? '') === 'yes';
            $status2 = $this->text($get('status2', $r));
            $notes = $this->text($get('Notes', $r));
            $descCol = $this->text($get('Description', $r));

            $rows[] = [
                'excel_row' => $r,
                'book_id' => $bookId,
                'product_id' => $productId,
                'name' => $this->text($get('Book Name', $r)),
                'sku' => $this->text($get('SKU', $r)),
                'price' => (float) str_replace(',', '', $get('Price', $r) ?? '0'),
                'status' => strtolower($get('Status', $r) ?? 'active') === 'active' ? 'active' : 'inactive',
                // Description column of the sheet is empty; the business wants Notes there.
                'description' => $notes ?? $descCol,
                'isbn' => $this->text($get('ISBN', $r)),
                'pages' => is_numeric($get('Pages', $r)) ? (int) $get('Pages', $r) : null,
                'cover_type' => strtolower($get('Cover Type', $r) ?? 'soft') === 'hard' ? 'hard' : 'soft',
                'published_at' => $this->date($get('Published Date', $r)),
                'language' => $this->language($this->text($get('Language', $r))),
                'is_translated' => $isTranslated ? 1 : 0,
                'translated_from' => $isTranslated ? $this->text($get('Translated From', $r)) : null,
                'translated_to' => $isTranslated ? $this->text($get('Translated To', $r)) : null,
                'translator_name' => $this->text($get('Translator', $r)),
                'supervisor' => $this->text($get('اشراف', $r)),
                'introduction_by' => $this->text($get('تقديم', $r)),
                'category' => $this->text($get('Category', $r)),
                'sub_category' => $this->text($get('Sub Category', $r)),
                'authors' => $this->text($get('Author 1', $r)),
                'contractor' => $this->text($get('المتعاقد', $r)),
                'nationality' => $this->text($get(['Nationality', 'الجنسية'], $r)),
                // status2 = "ملغي" means the contract ended
                'contract_status' => ($status2 !== null && str_contains($status2, 'ملغي')) ? 'ended' : 'active',
                'gift_copies' => is_numeric($get('نسخ الهدايا', $r)) ? (int) $get('نسخ الهدايا', $r) : 0,
                'deposit_number' => $this->text($get('رقم الإيداع', $r)),
                'deposit_year' => is_numeric($get('سنة الإيداع', $r)) ? (int) $get('سنة الإيداع', $r) : null,
                'deposit_status' => $this->text($get('حالة الإيداع', $r)),
            ];
        }

        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * Writes the rows in the exact column order of the `sheet_books` staging table used by
     * docs/sql/books-sync (phpMyAdmin import). Values are the normalised ones the sync uses, so
     * ISBNs stay full digits (a CSV saved from Excel would turn them into 9.78E+12).
     */
    public function exportStagingCsv(array $rows, string $path): void
    {
        $h = fopen($path, 'w');
        fputcsv($h, ['book_id', 'product_id', 'book_name', 'sku', 'price', 'status', 'description', 'isbn', 'pages', 'cover_type',
            'published_date', 'language', 'is_translated', 'translated_from', 'translated_to', 'translator', 'supervisor',
            'introduction', 'category', 'sub_category', 'used_in_sales', 'author1', 'contractor', 'nationality', 'notes',
            'status2', 'gift_copies', 'deposit_number', 'deposit_year', 'deposit_status']);
        foreach ($rows as $r) {
            fputcsv($h, [$r['book_id'], $r['product_id'], $r['name'], $r['sku'], $r['price'], $r['status'], '', $r['isbn'], $r['pages'],
                $r['cover_type'], $r['published_at'], $r['language'], $r['is_translated'] ? 'Yes' : 'No', $r['translated_from'],
                $r['translated_to'], $r['translator_name'], $r['supervisor'], $r['introduction_by'], $r['category'], $r['sub_category'],
                '', $r['authors'], $r['contractor'], $r['nationality'], $r['description'],
                $r['contract_status'] === 'ended' ? 'ملغي' : '', $r['gift_copies'], $r['deposit_number'], $r['deposit_year'], $r['deposit_status']]);
        }
        fclose($h);
    }

    /** Sheet sometimes spells the language out; the system stores ISO-style codes ('ar', 'en', ...). */
    private function language(?string $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $map = [
            'اللغة العربية' => 'ar', 'العربية' => 'ar', 'عربي' => 'ar', 'arabic' => 'ar',
            'اللغة الانجليزية' => 'en', 'اللغة الإنجليزية' => 'en', 'الانجليزية' => 'en', 'الإنجليزية' => 'en', 'english' => 'en',
            'اللغة الفرنسية' => 'fr', 'الفرنسية' => 'fr', 'french' => 'fr',
        ];

        return $map[mb_strtolower($v)] ?? $v;
    }

    /** Trim + collapse inner whitespace (Arabic names often carry double/NBSP spaces). */
    public function text(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', $value));

        return $value === '' ? null : $value;
    }

    private function cell(Worksheet $sheet, string $coord): ?string
    {
        $val = $sheet->getCell($coord)->getCalculatedValue();
        if ($val === null) {
            return null;
        }
        // ISBNs stored as numbers must not become 9.78977E+12
        $str = is_float($val) && floor($val) == $val ? sprintf('%.0f', $val) : trim((string) $val);

        return $str === '' ? null : $str;
    }

    private function date(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (is_numeric($value) && (float) $value >= 1000 && (float) $value <= 2999) {
            return ((int) $value) . '-01-01'; // a bare year typed as a number (not an Excel date serial)
        }
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        try {
            return (new \DateTime($value))->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
