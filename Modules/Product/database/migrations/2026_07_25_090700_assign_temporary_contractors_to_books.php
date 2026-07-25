<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Data migration step 4 (docs/contractor-migration-plan.md §5): create one `contractor_books`
     * row per book that had a real contract, choosing the temporary contractor as:
     *   the representative author (contract_authors.is_representative = true) if one exists,
     *   else the first linked author (lowest contract_authors.id).
     * This rule is explicitly temporary — the master Excel import (§6) supersedes it per book via
     * the المتعاقد column. Idempotent: skips books that already have a contractor_books row.
     */
    public function up(): void
    {
        $bookNameOnlyCount = DB::table('author_book_contracts')->whereNull('book_id')->count();
        if ($bookNameOnlyCount > 0) {
            Log::info("Author->Contractor migration: {$bookNameOnlyCount} contract(s) reference only a free-text book_name (no real book row) — their contractor was still migrated in step 2, but no contractor_books row can be created for them.");
        }

        $alreadyAssignedBookIds = DB::table('contractor_books')->pluck('book_id')->all();

        $contracts = DB::table('author_book_contracts')
            ->whereNotNull('book_id')
            ->whereNotIn('book_id', $alreadyAssignedBookIds ?: [0])
            ->select('id', 'book_id', 'contract_file', 'contract_date', 'percentage_from_book_profit')
            ->get();

        $now = now();
        $assigned = 0;
        $skipped = 0;

        foreach ($contracts as $contract) {
            $representativeAuthorId = DB::table('contract_authors')
                ->where('contract_id', $contract->id)
                ->orderByDesc('is_representative')
                ->orderBy('id')
                ->value('author_id');

            if (! $representativeAuthorId) {
                Log::warning("Author->Contractor migration: contract #{$contract->id} (book #{$contract->book_id}) has no linked author — skipping contractor_books row.");
                $skipped++;
                continue;
            }

            $contractorId = DB::table('contractors')->where('legacy_author_id', $representativeAuthorId)->value('id');

            if (! $contractorId) {
                Log::warning("Author->Contractor migration: no migrated contractor found for author #{$representativeAuthorId} (contract #{$contract->id}) — skipping.");
                $skipped++;
                continue;
            }

            try {
                DB::table('contractor_books')->insert([
                    'book_id' => $contract->book_id,
                    'contractor_id' => $contractorId,
                    'contract_file' => $contract->contract_file,
                    'profit_percentage' => $contract->percentage_from_book_profit,
                    'percentage_basis' => 'sale_price',
                    'contract_date' => $contract->contract_date,
                    'end_contract_date' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $assigned++;
            } catch (\Throwable $e) {
                Log::error("Author->Contractor migration: failed to create contractor_books for book #{$contract->book_id}: {$e->getMessage()}");
                $skipped++;
            }
        }

        Log::info('Author->Contractor migration: temporary contractor_books assignment complete.', [
            'assigned' => $assigned,
            'skipped' => $skipped,
        ]);
    }

    public function down(): void
    {
        // Best-effort/no-op: contractor_books rows created here are indistinguishable from ones
        // created afterward by normal use once the Excel import (§6) starts editing/replacing
        // them — matches the plan's documented forward-only caveat for this data migration.
    }
};
