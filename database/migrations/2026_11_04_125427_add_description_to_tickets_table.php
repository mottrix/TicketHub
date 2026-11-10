<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add optional detail text without requiring a value for existing tickets.
     * Current create/update requests require text, but legacy rows can still be null.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('description')->nullable();
        });
    }

    /**
     * Remove the column, losing any descriptions stored since it was added.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
