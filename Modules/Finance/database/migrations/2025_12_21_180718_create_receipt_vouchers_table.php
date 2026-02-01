<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->foreignId('party_id')->constrained('parties')->onDelete('restrict');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');
            $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->onDelete('restrict');
            
            $table->decimal('amount', 15, 2);
            $table->date('voucher_date');
            
            // Payment method
            $table->enum('payment_method', ['cash', 'cheque', 'bank_transfer', 'credit_card', 'other'])->default('cash');
            $table->string('reference_number')->nullable(); 
            
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_vouchers');
    }
};