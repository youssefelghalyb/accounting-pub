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
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->string('advance_code')->unique(); // e.g., ADV-2025-001
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2); // Amount advanced
            $table->date('issue_date'); // When advance was given
            $table->date('expected_settlement_date')->nullable(); // Expected return/settlement date
            $table->date('actual_settlement_date')->nullable(); // When fully settled
            $table->enum('type', ['cash', 'salary_advance', 'petty_cash', 'travel', 'purchase'])->default('cash');
            $table->enum('status', ['pending', 'partial_settlement', 'settled'])->default('pending');
            $table->text('purpose'); // Reason for advance
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('employees'); // Who issued
            $table->timestamps();
        });

        Schema::create('advance_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_code')->unique(); // e.g., SET-2025-001
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Direct employee link
            $table->foreignId('advance_id')->nullable()->constrained('employee_advances')->onDelete('set null'); // Optional link
            $table->decimal('cash_returned', 10, 2)->default(0); // Cash returned
            $table->decimal('amount_spent', 10, 2)->default(0); // Amount spent (with receipts)
            $table->date('settlement_date');
            $table->text('settlement_notes')->nullable();
            $table->string('receipt_file')->nullable(); // Path to receipt file
            $table->foreignId('received_by')->nullable()->constrained('employees'); // Who received return
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_settlements');
        Schema::dropIfExists('employee_advances');
    }
};