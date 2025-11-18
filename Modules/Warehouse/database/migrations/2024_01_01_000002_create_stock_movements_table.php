<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Reference number for the movement
            $table->string('reference_number')->unique();

            // Movement type
            $table->enum('type', ['in', 'out', 'transfer', 'adjustment'])->default('in');

            // Date of movement
            $table->date('movement_date');

            // Source and destination
            $table->string('source_warehouse')->nullable();
            $table->string('destination_warehouse')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Status
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');

            // Total items in this movement
            $table->integer('total_items')->default(0);

            // Audit fields
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('edited_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index('reference_number');
            $table->index('type');
            $table->index('movement_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
