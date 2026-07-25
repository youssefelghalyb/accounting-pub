<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Final, irreversible step of the Author -> Contractor migration
     * (docs/contractor-migration-plan.md §5 step 7 / §8 Phase 8): drops the legacy
     * Author/Contract tables now that every Author has been migrated to a Contractor,
     * every book's contract to a contractor_books row, every historical sale to a
     * book_sales row, and every historical payment to a contract_transactions row
     * (migrations 090500-090900). Dropped in FK-safe order (children before parents).
     *
     * down() restores the table *shapes* only (matching their final pre-drop state) — it
     * cannot reverse-derive Contractor/ContractorBook/BookSale/ContractTransaction data back
     * into these tables. This is expected for a forward-only business migration.
     */
    public function up(): void
    {
        Schema::dropIfExists('contract_authors');
        Schema::dropIfExists('author_contract_transactions');
        Schema::dropIfExists('author_book_contracts');
        Schema::dropIfExists('authors');
    }

    public function down(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('full_name');
            $table->string('nationality', 150)->nullable();
            $table->string('country_of_residence', 150)->nullable();
            $table->text('bio')->nullable();
            $table->string('occupation')->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('whatsapp_number', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('id_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('author_book_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->nullable()->constrained('books')->onDelete('cascade');
            $table->string('book_name')->nullable();
            $table->date('contract_date');
            $table->decimal('contract_price', 12, 2)->default(0);
            $table->decimal('percentage_from_book_profit', 5, 2)->default(0);
            $table->string('contract_file')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('contract_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('author_book_contracts')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('authors')->onDelete('cascade');
            $table->boolean('is_representative')->default(false);
            $table->timestamps();

            $table->unique(['contract_id', 'author_id']);
        });

        Schema::create('author_contract_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('author_book_contracts')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->string('receipt_file')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }
};
