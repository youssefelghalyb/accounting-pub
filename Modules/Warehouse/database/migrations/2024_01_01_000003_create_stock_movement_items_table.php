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
        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();

            // Stock movement relationship
            $table->foreignId('stock_movement_id')
                ->constrained('stock_movements')
                ->onDelete('cascade');

            // Product relationship
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            // Quantity
            $table->integer('quantity');

            // Notes for this specific item
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('stock_movement_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
    }
};
