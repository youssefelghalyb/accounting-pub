<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for the Books grid editor: one row per applied change, grouped by batch_uuid, so the
 * grid can be the "source of truth" and every edit/add/delete stays attributable and reversible by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_grid_audits', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_uuid')->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 30); // add | change | delete | stock | category_create | contractor_create | contractor_update
            $table->unsignedBigInteger('book_id')->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('field', 60)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->longText('snapshot')->nullable(); // full row (json) for add/delete
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_grid_audits');
    }
};
