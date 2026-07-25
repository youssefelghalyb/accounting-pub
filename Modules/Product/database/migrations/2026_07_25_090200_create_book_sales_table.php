<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable royalty snapshot — one row per sold sales_invoice_item.
     * Never updated after creation; percentages/prices are copied at sale time
     * so historical royalty figures never change even if the contract terms do.
     */
    public function up(): void
    {
        Schema::create('book_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('contractor_book_id')->constrained('contractor_books')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('sales_invoices')->onDelete('cascade');
            $table->foreignId('invoice_item_id')->constrained('sales_invoice_items')->onDelete('cascade');
            $table->decimal('sale_price_snapshot', 10, 2)->nullable();
            $table->decimal('base_price_snapshot', 10, 2)->nullable();
            $table->decimal('percentage_snapshot', 5, 2);
            $table->enum('percentage_basis_snapshot', ['base_price', 'sale_price']);
            $table->decimal('contractor_profit', 12, 2)->default(0);
            $table->boolean('is_gift')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_sales');
    }
};
