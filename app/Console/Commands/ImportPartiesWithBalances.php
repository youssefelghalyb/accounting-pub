<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Modules\Finance\Models\Party;
use Modules\Finance\Models\SalesInvoice;
use Modules\Finance\Models\PurchaseInvoice;

class ImportPartiesWithBalances extends Command
{
    protected $signature = 'import:parties-balances 
                            {file : Path to the xlsx file}
                            {--user_id=1 : created_by user id}';

    protected $description = 'Import parties with their opening balances from Excel';

    public function handle()
    {
        $filePath = $this->argument('file');
        $userId = (int) $this->option('user_id');

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

        // Find header row
        $header = array_map(fn ($h) => trim((string)$h), $rows[0]);

        $getIndex = function (string $colName) use ($header) {
            foreach ($header as $i => $name) {
                if (trim($name) === trim($colName)) return $i;
            }
            return null;
        };

        $idxClientNumber = $getIndex('رقم العميل');
        $idxClientName = $getIndex('اسم العميل');
        $idxBalance = $getIndex('رصيد');

        if ($idxClientNumber === null || $idxClientName === null || $idxBalance === null) {
            $this->error("Missing required columns. Expected: رقم العميل, اسم العميل, رصيد");
            return self::FAILURE;
        }

        $createdParties = 0;
        $createdSalesInvoices = 0;
        $createdPurchaseInvoices = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            // Start from row 1 (skip header)
            for ($r = 1; $r < count($rows); $r++) {
                $row = $rows[$r];

                $clientNumber = trim((string)($row[$idxClientNumber] ?? ''));
                $clientName = trim((string)($row[$idxClientName] ?? ''));
                $balanceRaw = trim((string)($row[$idxBalance] ?? ''));

                // Skip empty rows
                if ($clientNumber === '' || $clientName === '') {
                    continue;
                }

                // Clean balance (remove commas and convert to float)
                $balance = (float) str_replace(',', '', $balanceRaw);

                // Create or find party
                $party = Party::where('name', $clientName)->first();
                
                if (!$party) {
                    $party = Party::create([
                        'name' => $clientName,
                        'type' => 'company',
                        'phone' => $clientNumber, // Store client number in phone field
                        'is_active' => true,
                        'created_by' => $userId,
                        'edited_by' => $userId,
                    ]);
                    $createdParties++;
                    $this->line("Created party: {$clientName}");
                }

                // Skip if balance is zero
                if ($balance == 0) {
                    $skipped++;
                    continue;
                }

                // Create invoice based on balance
                $invoiceDate = now();

                if ($balance > 0) {
                    // Positive balance = Customer owes us = Sales Invoice
                    $invoiceNumber = $this->generateSalesInvoiceNumber();

                    SalesInvoice::create([
                        'invoice_number' => $invoiceNumber,
                        'party_id' => $party->id,
                        'invoice_date' => $invoiceDate,
                        'due_date' => null,
                        'subtotal' => $balance,
                        'discount_amount' => 0,
                        'discount_type' => 'fixed',
                        'discount_value' => 0,
                        'is_taxable' => false,
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                        'total_amount' => $balance,
                        'paid_amount' => 0,
                        'status' => 'unpaid',
                        'payment_terms' => null,
                        'notes' => 'رصيد افتتاحي - Opening Balance',
                        'terms_conditions' => null,
                        'created_by' => $userId,
                        'edited_by' => $userId,
                    ]);

                    $createdSalesInvoices++;
                    $this->info("  → Sales Invoice {$invoiceNumber}: {$balance}");

                } else {
                    // Negative balance = We owe vendor = Purchase Invoice
                    $invoiceNumber = $this->generatePurchaseInvoiceNumber();
                    $absoluteBalance = abs($balance);

                    PurchaseInvoice::create([
                        'invoice_number' => $invoiceNumber,
                        'party_id' => $party->id,
                        'invoice_date' => $invoiceDate,
                        'due_date' => null,
                        'subtotal_amount' => $absoluteBalance,
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                        'discount_amount' => 0,
                        'total_amount' => $absoluteBalance,
                        'paid_amount' => 0,
                        'outstanding_balance' => $absoluteBalance,
                        'status' => 'unpaid',
                        'notes' => 'رصيد افتتاحي - Opening Balance',
                        'reference_number' => null,
                        'created_by' => $userId,
                        'edited_by' => $userId,
                    ]);

                    $createdPurchaseInvoices++;
                    $this->info("  → Purchase Invoice {$invoiceNumber}: {$absoluteBalance}");
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
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("Parties created: {$createdParties}");
        $this->line("Sales Invoices created: {$createdSalesInvoices}");
        $this->line("Purchase Invoices created: {$createdPurchaseInvoices}");
        $this->line("Rows with zero balance (skipped): {$skipped}");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        return self::SUCCESS;
    }

    /**
     * Generate unique sales invoice number
     */
    private function generateSalesInvoiceNumber(): string
    {
        $year = date('Y');
        $lastInvoice = SalesInvoice::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'SI-' . $year . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique purchase invoice number
     */
    private function generatePurchaseInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = PurchaseInvoice::withTrashed()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'PI-' . $year . '-' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}