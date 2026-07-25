<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Authors are no longer a business entity — just metadata displayed on the book,
     * stored as a comma-separated string (e.g. "Stephen Hawking, Leonard Mlodinow").
     * The legal/financial party for a book's contract is now the Contractor
     * (see contractor_books / contractors tables), not an author.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->text('authors')->nullable()->after('product_id');
            $table->string('supervisor')->nullable()->after('translator_name');
            $table->string('introduction_by')->nullable()->after('supervisor');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['authors', 'supervisor', 'introduction_by']);
        });
    }
};
