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
        Schema::table('our_technic_ticket_reports', function (Blueprint $table) {
            $table->float('hours', 3, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // There is no turning back
    }
};
