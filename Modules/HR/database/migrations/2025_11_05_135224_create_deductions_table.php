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
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['days', 'amount', 'unpaid_leave']); // Type of deduction
            $table->integer('days')->nullable(); // Number of days to deduct
            $table->decimal('amount', 10, 2)->nullable(); // Fixed amount or calculated from days
            $table->date('deduction_date');
            $table->foreignId('leave_id')->nullable()->constrained()->onDelete('set null'); // Link to leave if applicable
            $table->text('reason');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
