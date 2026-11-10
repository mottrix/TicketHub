<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the original ticket fields before later migrations add ownership/details.
     * ticket_code is the public unique number; id is the key used by routes/relations.
     * Priority/status are plain strings, so accepted values are enforced by controllers.
     * The database defaults to pending, while the current creation form saves open.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->Integer('ticket_code')->unique();
            $table->string('ticket_name');
            $table->string('ticket_priority');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Remove the tickets table and its stored data when this migration is rolled back.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
