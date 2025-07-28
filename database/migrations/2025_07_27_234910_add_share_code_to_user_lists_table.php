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
        Schema::table('user_lists', function (Blueprint $table) {
            $table->string('share_code', 8)->unique()->nullable()->after('name');
            $table->boolean('is_public')->default(false)->after('share_code');
            $table->index('share_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_lists', function (Blueprint $table) {
            $table->dropIndex(['share_code']);
            $table->dropColumn(['share_code', 'is_public']);
        });
    }
};
