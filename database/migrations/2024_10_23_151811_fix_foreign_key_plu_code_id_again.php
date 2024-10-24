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
            // Drop the old foreign key constraint
            $table->dropForeign(['plu_code_id']);

            // Add the new foreign key constraint
            $table->foreign('plu_code_id')
                ->references('id')
                ->on('plu_codes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['plu_code_id']);

            // Add back the old foreign key constraint
            $table->foreign('plu_code_id')
                ->references('id')
                ->on('plu_codes')
                ->onDelete('cascade');
        });
    }
};
