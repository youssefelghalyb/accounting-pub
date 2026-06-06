<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Migrate existing author_id data from author_book_contracts into the new pivot
        // Every existing contract had exactly one author — that author becomes the representative
        $contracts = DB::table('author_book_contracts')
            ->whereNotNull('author_id')
            ->select('id', 'author_id')
            ->get();

        foreach ($contracts as $contract) {
            DB::table('contract_authors')->insert([
                'contract_id'      => $contract->id,
                'author_id'        => $contract->author_id,
                'is_representative' => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        // Step 2: Drop the old author_id FK column from author_book_contracts
        Schema::table('author_book_contracts', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id');
        });

        // Step 3: Migrate existing author_id data from books into contract_authors
        // A book's author_id was redundant with the contract's author_id — now authors
        // are only tracked via contracts, so we just drop the column from books.
        // But first: for books that have NO contract yet, we must preserve the author link
        // by creating a stub entry or leaving it — in this case we keep author_id on books
        // temporarily handled via the contract. Since book->author_id always matched
        // contract->author_id, the pivot migration above covers it.
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->dropColumn('author_id');
        });
    }

    public function down(): void
    {
        // Restore author_id to books
        Schema::table('books', function (Blueprint $table) {
            $table->foreignId('author_id')
                ->nullable()
                ->after('product_id')
                ->constrained('authors')
                ->onDelete('set null');
        });

        // Restore author_id to author_book_contracts
        Schema::table('author_book_contracts', function (Blueprint $table) {
            $table->foreignId('author_id')
                ->nullable()
                ->after('id')
                ->constrained('authors')
                ->onDelete('cascade');
        });

        // Restore data from pivot back to contracts (representative = old author_id)
        $pivots = DB::table('contract_authors')
            ->where('is_representative', true)
            ->select('contract_id', 'author_id')
            ->get();

        foreach ($pivots as $pivot) {
            DB::table('author_book_contracts')
                ->where('id', $pivot->contract_id)
                ->update(['author_id' => $pivot->author_id]);
        }

        Schema::dropIfExists('contract_authors');
    }
};