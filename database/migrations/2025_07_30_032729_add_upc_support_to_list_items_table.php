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
            $table->enum('item_type', ['plu', 'upc'])->default('plu')->after('user_list_id')->index();
            $table->foreignId('upc_code_id')->nullable()->after('plu_code_id')->constrained('upc_codes')->onDelete('cascade');

            // Add index for efficient queries
            $table->index(['user_list_id', 'item_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->dropForeign(['upc_code_id']);
            $table->dropIndex(['user_list_id', 'item_type']);
        });

        Schema::table('list_items', function (Blueprint $table) {
            $table->dropColumn(['item_type', 'upc_code_id']);
        });
    }
};
