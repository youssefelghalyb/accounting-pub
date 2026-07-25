<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Data migration step 6 (docs/contractor-migration-plan.md §5, Revision 2): migrates every
     * historical `author_contract_transactions` row into the new, accounting-backed
     * `contract_transactions` model. Every ContractTransaction must reference exactly one real
     * voucher, so this synthesizes a PaymentVoucher per historical row (money the publishing
     * house paid the author, the old system's only direction) against:
     *   - the contractor's Party (auto-created here if the contractor doesn't have one yet —
     *     same rule as the live gift/transaction flows), and
     *   - one dedicated placeholder Account, "Legacy Contractor Payments (Migration)", since the
     *     old system never recorded which real cash/bank account these payments moved through.
     *     Flagged prominently: historical royalty figures live in this bucket, not a real
     *     account, until/unless reconciled by hand (see plan §9 open question).
     *
     * Idempotent via `legacy_transaction_id`. Never modifies author_contract_transactions itself.
     */
    public function up(): void
    {
        $legacyAccountId = $this->findOrCreateLegacyAccount();

        $alreadyMigrated = DB::table('contract_transactions')
            ->whereNotNull('legacy_transaction_id')
            ->pluck('legacy_transaction_id')
            ->all();

        $oldTransactions = DB::table('author_contract_transactions')
            ->whereNotIn('id', $alreadyMigrated ?: [0])
            ->get();

        $now = now();
        $migrated = 0;
        $skipped = 0;

        foreach ($oldTransactions as $oldTx) {
            $contractorBookId = $this->resolveContractorBookId($oldTx->contract_id);

            if (! $contractorBookId) {
                Log::warning("Author->Contractor migration: no contractor_books row for contract #{$oldTx->contract_id} — skipping historical transaction #{$oldTx->id}.");
                $skipped++;
                continue;
            }

            $contractorId = DB::table('contractor_books')->where('id', $contractorBookId)->value('contractor_id');
            $partyId = $this->ensureContractorHasParty($contractorId);

            if (! $partyId) {
                Log::error("Author->Contractor migration: could not resolve/create a Party for contractor #{$contractorId} — skipping historical transaction #{$oldTx->id}.");
                $skipped++;
                continue;
            }

            try {
                DB::beginTransaction();

                $voucherId = DB::table('payment_vouchers')->insertGetId([
                    'voucher_number' => 'PV-MIGR-' . $oldTx->id,
                    'party_id' => $partyId,
                    'account_id' => $legacyAccountId,
                    'purchase_invoice_id' => null,
                    'voucher_date' => $oldTx->payment_date,
                    'amount' => $oldTx->amount,
                    'payment_method' => 'cash',
                    'description' => "Migrated from legacy author contract transaction #{$oldTx->id}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('contract_transactions')->insert([
                    'contractor_book_id' => $contractorBookId,
                    'type' => 'royalty_payment',
                    'amount' => $oldTx->amount,
                    'transaction_date' => $oldTx->payment_date,
                    'notes' => $oldTx->notes,
                    'receipt_file' => $oldTx->receipt_file,
                    'receipt_voucher_id' => null,
                    'payment_voucher_id' => $voucherId,
                    'legacy_transaction_id' => $oldTx->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::commit();
                $migrated++;
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Author->Contractor migration: failed to migrate historical transaction #{$oldTx->id}: {$e->getMessage()}");
                $skipped++;
            }
        }

        Log::info('Author->Contractor migration: author_contract_transactions -> contract_transactions complete.', [
            'migrated' => $migrated,
            'skipped' => $skipped,
        ]);
    }

    public function down(): void
    {
        $migratedRows = DB::table('contract_transactions')->whereNotNull('legacy_transaction_id')->get(['id', 'payment_voucher_id']);

        DB::table('contract_transactions')->whereNotNull('legacy_transaction_id')->delete();

        $voucherIds = $migratedRows->pluck('payment_voucher_id')->filter()->all();
        if ($voucherIds) {
            DB::table('payment_vouchers')->whereIn('id', $voucherIds)->delete();
        }

        // Drop the placeholder account too, but only if nothing else ended up using it.
        $legacyAccountId = DB::table('accounts')->where('account_name', 'Legacy Contractor Payments (Migration)')->value('id');
        if ($legacyAccountId && ! DB::table('payment_vouchers')->where('account_id', $legacyAccountId)->exists()
            && ! DB::table('receipt_vouchers')->where('account_id', $legacyAccountId)->exists()) {
            DB::table('accounts')->where('id', $legacyAccountId)->delete();
        }
    }

    private function findOrCreateLegacyAccount(): int
    {
        $name = 'Legacy Contractor Payments (Migration)';

        $existingId = DB::table('accounts')->where('account_name', $name)->value('id');
        if ($existingId) {
            return $existingId;
        }

        $now = now();

        return DB::table('accounts')->insertGetId([
            'account_name' => $name,
            'account_type' => 'cash',
            'opening_balance' => 0,
            'currency' => 'USD',
            'notes' => 'Placeholder account created by the Author->Contractor data migration to attribute '
                . 'historical author payments whose original cash/bank account was never recorded. '
                . 'See docs/contractor-migration-plan.md §5/§9.',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * A legacy author_contract_transactions row points at author_book_contracts.id (contract_id).
     * Map that contract -> its book -> the contractor_books row created in step 4.
     */
    private function resolveContractorBookId(int $legacyContractId): ?int
    {
        $bookId = DB::table('author_book_contracts')->where('id', $legacyContractId)->value('book_id');

        if (! $bookId) {
            return null;
        }

        return DB::table('contractor_books')->where('book_id', $bookId)->value('id');
    }

    private function ensureContractorHasParty(int $contractorId): ?int
    {
        $contractor = DB::table('contractors')->where('id', $contractorId)->first();

        if (! $contractor) {
            return null;
        }

        if ($contractor->party_id) {
            return $contractor->party_id;
        }

        $now = now();

        $partyId = DB::table('parties')->insertGetId([
            'name' => $contractor->name,
            'type' => 'individual',
            'phone' => $contractor->phone,
            'email' => $contractor->email,
            'address' => $contractor->address,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contractors')->where('id', $contractorId)->update([
            'party_id' => $partyId,
            'updated_at' => $now,
        ]);

        return $partyId;
    }
};
