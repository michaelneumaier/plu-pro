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
        // Add marketplace columns to user_lists table
        Schema::table('user_lists', function (Blueprint $table) {
            $table->boolean('marketplace_enabled')->default(false)->after('is_public');
            $table->string('marketplace_title')->nullable()->after('marketplace_enabled');
            $table->text('marketplace_description')->nullable()->after('marketplace_title');
            $table->string('marketplace_category', 50)->nullable()->after('marketplace_description');
            $table->unsignedInteger('view_count')->default(0)->after('marketplace_category');
            $table->unsignedInteger('copy_count')->default(0)->after('view_count');
            $table->timestamp('published_at')->nullable()->after('copy_count');

            // Add indexes for performance
            $table->index('marketplace_enabled');
            $table->index('marketplace_category');
            $table->index('published_at');
        });

        // Create list_copies table to track copied lists
        Schema::create('list_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_list_id')->constrained('user_lists');
            $table->foreignId('copied_list_id')->constrained('user_lists')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Add unique constraint to prevent duplicate copies
            $table->unique(['original_list_id', 'copied_list_id']);

            // Add indexes for performance
            $table->index('original_list_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop list_copies table
        Schema::dropIfExists('list_copies');

        // Remove marketplace columns from user_lists table
        Schema::table('user_lists', function (Blueprint $table) {
            $table->dropIndex(['marketplace_enabled']);
            $table->dropIndex(['marketplace_category']);
            $table->dropIndex(['published_at']);

            $table->dropColumn([
                'marketplace_enabled',
                'marketplace_title',
                'marketplace_description',
                'marketplace_category',
                'view_count',
                'copy_count',
                'published_at',
            ]);
        });
    }
};
