<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add ownership while allowing older tickets to remain unowned; no backfill runs.
     * Deleting a user deletes their owned tickets rather than leaving user_id null.
     * The later messages migration also cascades deletion from tickets to replies.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    /**
     * Remove the ownership foreign key and column, losing existing owner assignments.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
