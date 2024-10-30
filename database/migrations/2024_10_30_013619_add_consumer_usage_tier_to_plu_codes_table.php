<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('plu_codes', function (Blueprint $table) {
            $table->enum('consumer_usage_tier', ['High', 'Medium', 'Low'])->default('Low')->after('Language');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plu_codes', function (Blueprint $table) {
            $table->dropColumn('consumer_usage_tier');
        });
    }
};
