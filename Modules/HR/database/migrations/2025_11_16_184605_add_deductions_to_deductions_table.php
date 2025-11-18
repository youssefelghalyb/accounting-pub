<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deductions', function (Blueprint $table) {
            $table->foreignId('advance_id')->nullable()->after('leave_id')->constrained('employee_advances')->onDelete('set null');
        });

        // Update advance status enum to include new status
        Schema::table('employee_advances', function (Blueprint $table) {
            // Drop and recreate the status column with new values
            $table->dropColumn('status');
        });

        Schema::table('employee_advances', function (Blueprint $table) {
            $table->enum('status', ['pending', 'partial_settlement', 'settled', 'settled_via_deduction'])->default('pending')->after('type');
        });

        // Update deduction type enum to include advance_recovery
        Schema::table('deductions', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('deductions', function (Blueprint $table) {
            $table->enum('type', ['days', 'amount', 'unpaid_leave', 'advance_recovery'])->default('amount')->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('deductions', function (Blueprint $table) {
            $table->dropForeign(['advance_id']);
            $table->dropColumn('advance_id');
        });
    }
};