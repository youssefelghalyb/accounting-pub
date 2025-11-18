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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            // Product relationship
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            // Warehouse/Location information
            $table->string('warehouse_name');
            $table->string('location')->nullable();
            $table->text('description')->nullable();

            // Stock quantity
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->storedAs('quantity - reserved_quantity');

            // Stock status
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Minimum stock level for alerts
            $table->integer('minimum_quantity')->default(0);

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
            $table->index('product_id');
            $table->index('warehouse_name');
            $table->index('status');
            $table->unique(['product_id', 'warehouse_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
