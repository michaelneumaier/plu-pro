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
        Schema::create('plu_codes', function (Blueprint $table) {
            $table->integer('id')->primary(); // Use integer for 'id' as per CSV
            $table->string('plu')->unique();
            $table->string('type');
            $table->string('category');
            $table->string('commodity');
            $table->string('variety');
            $table->string('size')->nullable();
            $table->string('measures_na')->nullable();
            $table->string('measures_row')->nullable();
            $table->text('restrictions')->nullable();
            $table->string('botanical')->nullable();
            $table->string('aka')->nullable();
            $table->string('status');
            $table->string('link')->nullable();
            $table->text('notes')->nullable();
            $table->string('updated_by');
            $table->string('language')->nullable();
            $table->timestamps();
            $table->softDeletes(); // For 'deleted_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plu_codes');
    }
};
