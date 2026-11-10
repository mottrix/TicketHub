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
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique('tickets_ticket_code_unique');
            $table->dropColumn('ticket_code');
        });
    }

    /**
     * Restore an optional unique code. Removed codes cannot be recovered on rollback.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('ticket_code')->nullable()->unique();
        });
    }
};
