<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Data migration step 3 (docs/contractor-migration-plan.md §5): populate the new
     * `books.authors` free-text column from the legacy contract_authors/author_book_contracts
     * pivot, representative author first. Only touches rows where `authors` is still NULL, so
     * it's safe to re-run and never overwrites a value set some other way.
     */
    public function up(): void
    {
        $rows = DB::table('author_book_contracts as c')
            ->join('contract_authors as ca', 'ca.contract_id', '=', 'c.id')
            ->join('authors as a', 'a.id', '=', 'ca.author_id')
            ->whereNotNull('c.book_id')
            ->orderBy('c.book_id')
            ->orderByDesc('ca.is_representative')
            ->orderBy('ca.id')
            ->select('c.book_id', 'a.full_name')
            ->get()
            ->groupBy('book_id');

        $updated = 0;

        foreach ($rows as $bookId => $authorRows) {
            $namesText = $authorRows->pluck('full_name')->implode(', ');

            $updated += DB::table('books')
                ->where('id', $bookId)
                ->whereNull('authors')
                ->update(['authors' => $namesText]);
        }

        Log::info('Author->Contractor migration: books.authors backfilled.', ['books_updated' => $updated]);
    }

    public function down(): void
    {
        // Not safely reversible in isolation (can't tell backfilled values apart from values set
        // afterward by normal use) — leaving books.authors populated is harmless on rollback.
    }
};
