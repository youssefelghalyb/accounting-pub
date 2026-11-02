<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One row per saved Books-grid batch (what the History screen lists and what "Restore" works on).
 * Existing audit rows (created before this table existed) are back-filled so they show up too.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_grid_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('warehouse_id')->nullable(); // warehouse the stock edits of this batch were made in
            $table->unsignedInteger('added')->default(0);
            $table->unsignedInteger('changed')->default(0);
            $table->unsignedInteger('deleted')->default(0);
            $table->unsignedInteger('stock_changes')->default(0);
            $table->uuid('restore_of')->nullable()->index();   // this batch restored (reverted) that batch
            $table->uuid('reverted_by')->nullable()->index();  // that batch restored this one (a batch can be restored once)
            $table->timestamp('created_at')->useCurrent();
        });

        DB::statement("
            INSERT INTO book_grid_batches (batch_uuid, user_id, added, changed, deleted, stock_changes, created_at)
            SELECT batch_uuid, MIN(user_id),
                   SUM(CASE WHEN action = 'add' THEN 1 ELSE 0 END),
                   COUNT(DISTINCT CASE WHEN action = 'change' THEN book_id END),
                   SUM(CASE WHEN action = 'delete' THEN 1 ELSE 0 END),
                   SUM(CASE WHEN action = 'change' AND field = 'quantity' THEN 1 ELSE 0 END),
                   MIN(created_at)
            FROM book_grid_audits GROUP BY batch_uuid
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('book_grid_batches');
    }
};
