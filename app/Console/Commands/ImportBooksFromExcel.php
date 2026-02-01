<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\IOFactory;

use Modules\Product\Models\Product;
use Modules\Product\Models\Book;
use Modules\Product\Models\Author;
use Modules\Product\Models\BookCategory;

class ImportBooksFromExcel extends Command
{
    protected $signature = 'import:books-excel
                            {file : Path to the xlsx file (example: storage/app/imports/Book1.xlsx)}
                            {--user_id=1 : created_by user id}
                            {--show-skipped : Show detailed list of skipped rows}
                            {--limit=20 : Limit number of skipped rows to display}';

    protected $description = 'Import books from Excel and create categories, authors, products, and books';

    public function handle()
    {
        $filePath = $this->argument('file');
        $userId = (int) $this->option('user_id');
        $showSkipped = $this->option('show-skipped');
        $displayLimit = (int) $this->option('limit');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $this->info("Reading Excel: {$filePath}");

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            $this->error("Excel file contains no data rows.");
            return self::FAILURE;
        }

        // Header row
        $header = array_map(fn($h) => trim((string)$h), $rows[0]);

        $getIndex = function (string $colName) use ($header) {
            foreach ($header as $i => $name) {
                if (trim($name) === trim($colName)) return $i;
            }
            return null;
        };

        $idxTitle    = $getIndex('عنوان الكتاب');
        $idxAuthor   = $getIndex('المؤلف');
        $idxCategory = $getIndex('التصنيف');
        $idxPrice    = $getIndex("السعر \nبالجنية المصري ");
        $idxPages    = $getIndex("عدد\nالصفحات");
        $idxYear     = $getIndex('سنة النشر');
        $idxISBN     = $getIndex('الترقيم الدولي');

        $required = [
            'عنوان الكتاب' => $idxTitle,
            'المؤلف' => $idxAuthor,
            'التصنيف' => $idxCategory,
        ];

        foreach ($required as $name => $idx) {
            if ($idx === null) {
                $this->error("Missing column in Excel header: {$name}");
                $this->line("Excel header found: " . implode(" | ", $header));
                return self::FAILURE;
            }
        }

        $createdProducts = 0;
        $createdBooks = 0;
        $skipped = 0;
        $skippedRows = [];
        $generatedISBNs = 0;

        DB::beginTransaction();

        try {

            // Start from row 2 (index 1)
            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];
                $rowNumber = $r + 1; // Excel row number (1-indexed + header)

                $title = trim((string)($row[$idxTitle] ?? ''));
                $authorName = trim((string)($row[$idxAuthor] ?? ''));
                $categoryName = trim((string)($row[$idxCategory] ?? ''));
                $isbnRaw = trim((string)($row[$idxISBN] ?? ''));

                $skipReason = null;

                // Check for missing required fields
                if ($title === '') {
                    $skipReason = 'Missing title';
                }

                if ($skipReason) {
                    $skipped++;
                    $skippedRows[] = [
                        'row_number' => $rowNumber,
                        'title' => $title ?: 'N/A',
                        'author' => $authorName ?: 'N/A',
                        'isbn' => $isbnRaw ?: 'N/A',
                        'reason' => $skipReason,
                    ];
                    continue;
                }

                // ISBN cleanup (keep digits only)
                $isbn = preg_replace('/\D+/', '', $isbnRaw);
                $isbnGenerated = false;

                // Check if ISBN is missing or empty
                if (empty($isbn)) {
                    $isbn = $this->generateUniqueISBN();
                    $isbnGenerated = true;
                    $generatedISBNs++;
                }

                // Check for duplicate ISBN
                $existingBook = Book::where('isbn', $isbn)->first();
                if ($existingBook) {
                    // Generate new ISBN for duplicate
                    $isbn = $this->generateUniqueISBN();
                    $isbnGenerated = true;
                    $generatedISBNs++;
                }

                // ---- Category ----
                $categoryId = null;
                if ($categoryName !== '') {
                    $category = BookCategory::firstOrCreate(
                        ['name' => $categoryName],
                        [
                            'slug' => Str::slug($categoryName),
                            'status' => 'active',
                            'created_by' => $userId,
                        ]
                    );
                    $categoryId = $category->id;
                }

                // ---- Author ----
                $authorId = null;
                if ($authorName !== '') {
                    $author = Author::firstOrCreate(
                        ['full_name' => $authorName],
                        []
                    );
                    $authorId = $author->id;
                }

                // ---- Price / Pages / Year ----
                $price = (float)($row[$idxPrice] ?? 0);
                $pages = (int)($row[$idxPages] ?? 0);
                $year = (int)($row[$idxYear] ?? 0);
                $publishedAt = $year > 0 ? "{$year}-01-01" : null;

                // ---- Create Product ----
                // SKU strategy: BOOK-{ISBN}
                $sku = $isbn ? "BOOK-{$isbn}" : null;

                $product = Product::create([
                    'name' => $title,
                    'type' => 'book',
                    'sku' => $sku,
                    'description' => null,
                    'base_price' => $price > 0 ? $price : 0,
                    'status' => 'active',
                    'created_by' => $userId,
                ]);

                $createdProducts++;

                // ---- Create Book ----
                Book::create([
                    'product_id' => $product->id,
                    'author_id' => $authorId,
                    'category_id' => $categoryId,
                    'sub_category_id' => null,

                    'isbn' => $isbn,
                    'num_of_pages' => $pages > 0 ? $pages : null,

                    // default cover_type - change if your system has enum rules
                    'cover_type' => 'soft',
                    'published_at' => $publishedAt,
                    'language' => 'ar',
                    'is_translated' => false,

                    'translated_from' => null,
                    'translated_to' => null,
                    'translator_name' => null,

                    'created_by' => $userId,
                ]);

                $createdBooks++;

                // Log if ISBN was generated
                if ($isbnGenerated) {
                    $this->line("  → Row {$rowNumber}: Generated ISBN {$isbn} for '{$title}'");
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("✅ Import finished!");
        $this->line("Products created: {$createdProducts}");
        $this->line("Books created: {$createdBooks}");
        $this->line("ISBNs auto-generated: {$generatedISBNs}");
        $this->line("Rows skipped: {$skipped}");

        // Display skipped rows if requested or if there are any
        if (count($skippedRows) > 0) {
            $this->newLine();

            // Group by reason
            $groupedByReason = [];
            foreach ($skippedRows as $row) {
                $reason = $row['reason'];
                if (!isset($groupedByReason[$reason])) {
                    $groupedByReason[$reason] = [];
                }
                $groupedByReason[$reason][] = $row;
            }

            // Display summary
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📊 Skip Reasons Summary:");
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            foreach ($groupedByReason as $reason => $rows) {
                $this->line(sprintf("  • %-30s : %d", $reason, count($rows)));
            }

            // Show detailed list if requested
            if ($showSkipped || $this->option('verbose')) {
                $this->newLine();
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                $this->info("📋 Detailed Skipped Rows (showing first {$displayLimit}):");
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

                $displayCount = 0;
                foreach ($groupedByReason as $reason => $rows) {
                    if ($displayCount >= $displayLimit) break;

                    $this->newLine();
                    $this->warn("⚠️  {$reason} (" . count($rows) . " total):");

                    foreach ($rows as $row) {
                        if ($displayCount >= $displayLimit) break;

                        $this->line(sprintf(
                            "   Row %4d | ISBN: %-15s | %s | %s",
                            $row['row_number'],
                            $row['isbn'],
                            mb_substr($row['title'], 0, 40) . (mb_strlen($row['title']) > 40 ? '...' : ''),
                            mb_substr($row['author'], 0, 20)
                        ));

                        if (isset($row['existing_book_id'])) {
                            $this->line(sprintf(
                                "            └─ Already exists as Book ID: %d",
                                $row['existing_book_id']
                            ));
                        }

                        $displayCount++;
                    }
                }

                if ($skipped > $displayLimit) {
                    $this->newLine();
                    $this->comment("... and " . ($skipped - $displayLimit) . " more skipped rows");
                    $this->comment("Use --limit=N to show more rows");
                }
            } else {
                $this->newLine();
                $this->comment("💡 Use --show-skipped to see detailed list of skipped rows");
                $this->comment("   Example: php artisan import:books-excel {$filePath} --show-skipped");
            }

            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        }

        return self::SUCCESS;
    }

    /**
     * Generate a unique ISBN starting with 000
     */
    private function generateUniqueISBN(): string
    {
        do {
            // Generate a 13-digit ISBN starting with 000
            // Format: 000 + 10 random digits
            $isbn = '000' . str_pad((string)mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            
            // Check if this ISBN already exists
            $exists = Book::where('isbn', $isbn)->exists();
        } while ($exists);

        return $isbn;
    }
}