<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('party_id')->constrained('parties')->onDelete('restrict');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            
            // Amounts
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->enum('discount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('discount_value', 15, 2)->default(0);
            
            // Tax
            $table->boolean('is_taxable')->default(true);
            $table->decimal('tax_rate', 5, 2)->default(0); // From org settings or custom
            $table->decimal('tax_amount', 15, 2)->default(0);
            
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            
            // Status
            $table->enum('status', ['draft', 'pending', 'unpaid', 'partial', 'paid', 'cancelled'])->default('unpaid');
            
            // Payment terms
            $table->string('payment_terms')->nullable(); // e.g., "Net 30", "Due on Receipt"
            
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};