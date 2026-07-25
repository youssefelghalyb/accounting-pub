<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractor_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->unique()->constrained('books')->onDelete('cascade');
            $table->foreignId('contractor_id')->constrained('contractors')->onDelete('cascade');
            $table->string('contract_file')->nullable();
            $table->decimal('profit_percentage', 5, 2)->default(0);
            $table->enum('percentage_basis', ['base_price', 'sale_price'])->default('sale_price');
            $table->date('contract_date')->nullable();
            $table->date('end_contract_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_books');
    }
};
