<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Neutral, accounting-backed ledger for a contractor_book (publishing fees, royalty
     * payouts, advances, refunds, adjustments). Every row must reference exactly one of
     * receipt_voucher_id (money IN, from the contractor) or payment_voucher_id (money OUT,
     * to the contractor) — never both, never neither.
     *
     * Implemented for now on the Modules\Product\Models\ContractorTransaction class (table name
     * is already the final `contract_transactions`) — the still-active legacy
     * Modules\Product\Models\ContractTransaction (table author_contract_transactions) occupies
     * that class name until it is deleted; this gets a pure rename in the destructive migration
     * phase. See docs/contractor-migration-plan.md §2.2.
     */
    public function up(): void
    {
        Schema::create('contract_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_book_id')->constrained('contractor_books')->onDelete('cascade');
            $table->enum('type', ['publishing_fee', 'royalty_payment', 'advance_payment', 'refund', 'adjustment']);
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->string('receipt_file')->nullable();

            $table->foreignId('receipt_voucher_id')->nullable()->unique()
                ->constrained('receipt_vouchers')->onDelete('cascade');
            $table->foreignId('payment_voucher_id')->nullable()->unique()
                ->constrained('payment_vouchers')->onDelete('cascade');

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            // Migration-support column: traces which legacy `author_contract_transactions` row
            // this was backfilled from, and is the idempotency key for that one-time migration.
            // Not FK-constrained since that table is dropped in a later migration.
            $table->unsignedBigInteger('legacy_transaction_id')->nullable()->index();
            $table->timestamps();
        });

        $this->addExactlyOneVoucherCheck();
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_transactions');
    }

    /**
     * Defense-in-depth: DB-level CHECK enforcing exactly one of the two voucher FKs is set.
     * Also enforced in the application layer (ContractorTransaction::booted()'s `saving` guard),
     * which is what protects every write path the app itself uses (Eloquent). This CHECK is the
     * backstop against raw SQL that bypasses Eloquent entirely.
     *
     * MySQL (8.0.16+) and Postgres support adding a CHECK via `ALTER TABLE ... ADD CONSTRAINT`,
     * so it's applied there (older MySQL/MariaDB parses but silently ignores CHECK — logged, not
     * fatal, if that statement itself errors for some other reason).
     *
     * SQLite is skipped outright, not attempted-and-caught: confirmed by hand that SQLite's
     * ALTER TABLE has no ADD CONSTRAINT support at all (a CHECK there can only be declared inside
     * the original CREATE TABLE statement, which Laravel's schema builder has no fluent API for).
     * Since SQLite is this app's local/test driver only, the model-level guard is the sole
     * enforcement there — every automated test for the exactly-one-voucher invariant should
     * exercise it through the Eloquent model, not raw inserts.
     */
    private function addExactlyOneVoucherCheck(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Log::info('contract_transactions: exactly-one-voucher CHECK constraint not applied at the DB level on SQLite (unsupported via ALTER TABLE). Enforced via the ContractorTransaction model only.');

            return;
        }

        $constraint = <<<'SQL'
            alter table contract_transactions
            add constraint chk_contract_transactions_exactly_one_voucher
            check (
                (receipt_voucher_id is not null and payment_voucher_id is null)
                or
                (receipt_voucher_id is null and payment_voucher_id is not null)
            )
        SQL;

        try {
            match ($driver) {
                'pgsql', 'mysql' => Schema::getConnection()->statement($constraint),
                default => Log::warning("Skipped exactly-one-voucher CHECK constraint on contract_transactions: unrecognized driver [{$driver}]."),
            };
        } catch (\Throwable $e) {
            Log::warning('Could not add exactly-one-voucher CHECK constraint on contract_transactions (likely MySQL/MariaDB < 8.0.16, which silently ignores CHECK). Enforced in the application layer only.', [
                'error' => $e->getMessage(),
            ]);
        }
    }
};
