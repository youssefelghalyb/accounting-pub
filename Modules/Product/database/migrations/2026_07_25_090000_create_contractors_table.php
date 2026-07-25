<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('secondary_phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('secondary_email')->nullable();
            $table->string('nationality', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('national_id_file')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
