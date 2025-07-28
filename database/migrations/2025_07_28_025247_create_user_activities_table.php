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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'created_list', 'added_item', 'published_list', etc.
            $table->string('subject_type')->nullable(); // Model type (UserList, PLUCode, etc.)
            $table->unsignedBigInteger('subject_id')->nullable(); // Model ID
            $table->json('metadata')->nullable(); // Additional details about the action
            $table->string('description'); // Human-readable description
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
