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
        Schema::create('upc_codes', function (Blueprint $table) {
            $table->id();
            $table->string('upc', 13)->unique()->index();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('brand')->nullable()->index();
            $table->string('category')->index();
            $table->string('commodity')->index();
            $table->string('image_url', 500)->nullable();
            $table->boolean('has_image')->default(false)->index();
            $table->json('kroger_categories')->nullable();
            $table->json('api_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upc_codes');
    }
};
