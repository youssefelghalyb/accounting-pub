<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PRE-MIGRATION SEEDER
 * ─────────────────────────────────────────────────────────────────────────────
 * Run this BEFORE the two new migrations:
 *   php artisan db:seed --class="Modules\Product\Database\Seeders\CreateContractsFromBooksSeeder"
 *   php artisan migrate
 *
 * What it does:
 *   1. Reads every book that has an author_id set
 *   2. Creates an empty contract in author_book_contracts for that book
 *      (contract_price = 0, percentage = 0, no file, today's date)
 *   3. Inserts the author as representative into contract_authors pivot
 *
 * After this seeder runs, migration 000008 will find real data in
 * author_book_contracts and migrate it correctly into contract_authors,
 * then safely drop author_id from both tables.
 *
 * Safe to run multiple times — skips books that already have a contract.
 * ─────────────────────────────────────────────────────────────────────────────
 */
class CreateContractsFromBooksSeeder extends Seeder
{
    public function run(): void
    {
        $books = DB::table('books')
            ->whereNotNull('author_id')
            ->get(['id', 'author_id', 'product_id']);

        if ($books->isEmpty()) {
            $this->command->info('No books with author_id found. Nothing to seed.');
            return;
        }

        $this->command->info("Found {$books->count()} book(s) with author_id. Creating contracts...");

        $skipped = 0;
        $created = 0;

        foreach ($books as $book) {
            // Skip if this book already has a contract
            $existingContract = DB::table('author_book_contracts')
                ->where('book_id', $book->id)
                ->first();

            if ($existingContract) {
                $skipped++;
                continue;
            }

            // Get the book name from products table for book_name field
            $product = DB::table('products')->where('id', $book->product_id)->first();
            $bookName = $product?->name ?? "Book #{$book->id}";

            // Create the contract
            $contractId = DB::table('author_book_contracts')->insertGetId([
                'author_id'                    => $book->author_id, // still exists pre-migration
                'book_id'                      => $book->id,
                'book_name'                    => $bookName,
                'contract_date'                => now()->toDateString(),
                'contract_price'               => 0.00,
                'percentage_from_book_profit'  => 0.00,
                'contract_file'                => null,
                'created_by'                   => null,
                'edited_by'                    => null,
                'created_at'                   => now(),
                'updated_at'                   => now(),
            ]);

            $created++;

            $this->command->line("  ✓ Contract #{$contractId} created for book: {$bookName} → author_id: {$book->author_id}");
        }

        $this->command->info("Done. Created: {$created} | Skipped (already had contract): {$skipped}");
        $this->command->newLine();
        $this->command->warn('Now run: php artisan migrate');
    }
}