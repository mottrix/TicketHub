<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tickets')
            ->whereNull('parent_id')
            ->whereNull('title')
            ->update(['title' => DB::raw('ticket_name')]);

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('ticket_name');
        });
    }

    /**
     * Restore the current title as the legacy name; overwritten legacy names cannot be recovered.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('ticket_name')->nullable();
        });

        DB::table('tickets')->update(['ticket_name' => DB::raw('title')]);
    }
};
