<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('secondary_phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('secondary_email')->nullable();
            $table->string('nationality', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('national_id_file')->nullable();
            // Migration-support column (not part of the original spec's field list): traces which
            // legacy `authors` row this Contractor was derived from during the one-time Author ->
            // Contractor data migration. Doubles as the idempotency key so that migration is safely
            // re-runnable. Harmless to keep afterward as an audit trail; not FK-constrained since
            // the `authors` table is dropped in a later migration.
            $table->unsignedBigInteger('legacy_author_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
