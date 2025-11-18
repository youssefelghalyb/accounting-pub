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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('author_id')->nullable()->constrained('authors')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('book_categories')->onDelete('set null');
            $table->foreignId('sub_category_id')->nullable()->constrained('book_categories')->onDelete('set null');
            $table->string('isbn', 50);
            $table->integer('num_of_pages')->nullable();
            $table->enum('cover_type', ['hard', 'soft'])->default('soft');
            $table->date('published_at')->nullable();
            $table->string('language', 100)->nullable();
            $table->boolean('is_translated')->default(false);
            $table->string('translated_from', 100)->nullable();
            $table->string('translated_to', 100)->nullable();
            $table->string('translator_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('edited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
