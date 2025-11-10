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
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i:s');
            $table->string('default_language', 5)->default('en');
            $table->json('available_languages')->nullable();
            $table->string('currency')->default('USD');
            $table->string('currency_symbol')->default('$');
            $table->boolean('enable_notifications')->default(true);
            $table->boolean('enable_audit_logs')->default(true);
            $table->string('primary_color')->default('#3490dc');
            $table->string('secondary_color')->default('#ffed4a');
            $table->string('CEO_name')->nullable();
            $table->string('CEO_email')->nullable();
            $table->string('CEO_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
