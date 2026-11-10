<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the SQLite reference inline to avoid rebuilding tickets and cascading messages.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE tickets ADD COLUMN parent_id INTEGER REFERENCES tickets(id) ON DELETE CASCADE');
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->constrained('tickets')->cascadeOnDelete();
            });
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('title')->nullable();
        });
    }

    /**
     * Remove titles and parent assignments; reply rows and legacy messages remain.
     * Rollback cannot reconstruct the parent relationships once this column is removed.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE tickets DROP COLUMN parent_id');
        } else {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_id');
            });
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('title');
        });
    }
};
