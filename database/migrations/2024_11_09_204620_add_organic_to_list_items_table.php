<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->boolean('organic')->default(false);
        });
    }

    public function down()
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->dropColumn('organic');
        });
    }
};
