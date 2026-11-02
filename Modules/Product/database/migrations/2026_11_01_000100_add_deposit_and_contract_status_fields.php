<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fields coming from the master Excel sheet that had no home yet:
 *  - books:            رقم الإيداع / سنة الإيداع / حالة الإيداع (legal deposit info) and
 *                      contract_status (status2 = "ملغي" => 'ended') — display only.
 *  - contractor_books: gift_copies_given (نسخ الهدايا already handed to the author) and
 *                      status (same status2 flag, shown on the contract).
 * Purely additive & nullable/defaulted: no existing row, invoice or royalty record is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('deposit_number', 50)->nullable()->after('introduction_by');
            $table->unsignedSmallInteger('deposit_year')->nullable()->after('deposit_number');
            $table->string('deposit_status', 50)->nullable()->after('deposit_year');
            $table->string('contract_status', 20)->default('active')->after('deposit_status');
        });

        Schema::table('contractor_books', function (Blueprint $table) {
            $table->unsignedInteger('gift_copies_given')->default(0)->after('percentage_basis');
            $table->string('status', 20)->default('active')->after('gift_copies_given');
        });
    }

    public function down(): void
    {
        Schema::table('contractor_books', function (Blueprint $table) {
            $table->dropColumn(['gift_copies_given', 'status']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['deposit_number', 'deposit_year', 'deposit_status', 'contract_status']);
        });
    }
};
