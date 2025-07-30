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
        Schema::table('list_items', function (Blueprint $table) {
            // Make plu_code_id nullable since UPC items won't have PLU codes
            $table->foreignId('plu_code_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            // Revert back to non-nullable
            $table->foreignId('plu_code_id')->nullable(false)->change();
        });
    }
};
