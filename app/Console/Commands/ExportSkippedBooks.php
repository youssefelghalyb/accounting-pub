<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Modules\Product\Models\Book;

class ExportSkippedBooks extends Command
{
    protected $signature = 'export:skipped-books {input_file}';
    protected $description = 'Export skipped books to Excel with reasons';

    public function handle()
    {
        $inputFile = $this->argument('input_file');

        if (!file_exists($inputFile)) {
            $this->error("File not found: {$inputFile}");
            return self::FAILURE;
        }

        $this->info("Reading Excel: {$inputFile}");

        // Read input file
        $spreadsheet = IOFactory::load($inputFile);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (count($rows) < 2) {
            $this->error("Excel file contains no data rows.");
            return self::FAILURE;
        }

        // Get column indexes
        $header = array_map(fn ($h) => trim((string)$h), $rows[0]);
        $getIndex = function (string $colName) use ($header) {
            foreach ($header as $i => $name) {
                if (trim($name) === trim($colName)) return $i;
            }
            return null;
        };

        $idxTitle = $getIndex('عنوان الكتاب');
        $idxAuthor = $getIndex('المؤلف');
        $idxCategory = $getIndex('التصنيف');
        $idxISBN = $getIndex('الترقيم الدولي');

        // Collect skipped books
        $skippedBooks = [];

        for ($r = 1; $r < count($rows); $r++) {
            $row = $rows[$r];
            $rowNumber = $r + 1;

            $title = trim((string)($row[$idxTitle] ?? ''));
            $authorName = trim((string)($row[$idxAuthor] ?? ''));
            $categoryName = trim((string)($row[$idxCategory] ?? ''));
            $isbnRaw = trim((string)($row[$idxISBN] ?? ''));

            // Check if should be skipped
            $skipReason = null;

            if ($title === '') {
                $skipReason = 'عنوان الكتاب فارغ';
            } elseif ($isbnRaw === '') {
                $skipReason = 'الترقيم الدولي فارغ';
            } else {
                // Check for duplicate ISBN
                $isbn = preg_replace('/\D+/', '', $isbnRaw);
                $existingBook = Book::where('isbn', $isbn)->first();
                
                if ($existingBook) {
                    $skipReason = "ISBN مكرر - الكتاب موجود برقم {$existingBook->id}";
                }
            }

            if ($skipReason) {
                $skippedBooks[] = [
                    'row_number' => $rowNumber,
                    'isbn' => $isbnRaw,
                    'title' => $title,
                    'author' => $authorName,
                    'category' => $categoryName,
                    'reason' => $skipReason,
                ];
            }
        }

        // Create Excel file
        $this->info("Creating Excel file with " . count($skippedBooks) . " skipped books...");

        $outputSpreadsheet = new Spreadsheet();
        $outputSheet = $outputSpreadsheet->getActiveSheet();
        $outputSheet->setRightToLeft(true); // RTL for Arabic

        // Set headers
        $outputSheet->setCellValue('A1', 'رقم السطر');
        $outputSheet->setCellValue('B1', 'الترقيم الدولي');
        $outputSheet->setCellValue('C1', 'عنوان الكتاب');
        $outputSheet->setCellValue('D1', 'المؤلف');
        $outputSheet->setCellValue('E1', 'التصنيف');
        $outputSheet->setCellValue('F1', 'سبب التجاهل');

        // Style header row
        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $outputSheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        $outputSheet->getStyle('A1:F1')->getFont()->getColor()->setRGB('FFFFFF');

        // Add data
        $rowNum = 2;
        foreach ($skippedBooks as $book) {
            $outputSheet->setCellValue("A{$rowNum}", $book['row_number']);
            $outputSheet->setCellValue("B{$rowNum}", $book['isbn']);
            $outputSheet->setCellValue("C{$rowNum}", $book['title']);
            $outputSheet->setCellValue("D{$rowNum}", $book['author']);
            $outputSheet->setCellValue("E{$rowNum}", $book['category']);
            $outputSheet->setCellValue("F{$rowNum}", $book['reason']);

            // Alternate row colors
            if ($rowNum % 2 == 0) {
                $outputSheet->getStyle("A{$rowNum}:F{$rowNum}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F2F2F2');
            }

            $rowNum++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $outputSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $outputSheet->getStyle("A1:F" . ($rowNum - 1))->applyFromArray($styleArray);

        // Save file
        $outputFileName = 'skipped_books_' . date('Y_m_d_His') . '.xlsx';
        $outputPath = storage_path('app/imports/' . $outputFileName);

        $writer = new Xlsx($outputSpreadsheet);
        $writer->save($outputPath);

        $this->newLine();
        $this->info("✅ Excel file created successfully!");
        $this->line("📁 File saved to: {$outputPath}");
        $this->line("📊 Total skipped books: " . count($skippedBooks));
        $this->newLine();

        return self::SUCCESS;
    }
}