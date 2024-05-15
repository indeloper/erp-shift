<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->renameColumn('start_location', 'start_location_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('our_technics', function (Blueprint $table) {
            $table->renameColumn('start_location_id', 'start_location');
        });
    }
};
