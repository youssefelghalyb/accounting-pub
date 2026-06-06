<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')
                ->constrained('author_book_contracts')
                ->onDelete('cascade');
            $table->foreignId('author_id')
                ->constrained('authors')
                ->onDelete('cascade');
            $table->boolean('is_representative')->default(false);
            $table->timestamps();

            $table->unique(['contract_id', 'author_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_authors');
    }
};