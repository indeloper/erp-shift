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
        Schema::table('commercial_offer_notes', function (Blueprint $table) {
            $table->text('note')->change();
        });

        Schema::table('commercial_offer_requirements', function (Blueprint $table) {
            $table->text('requirement')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('commercial_offer_notes', function (Blueprint $table) {
            $table->string('note')->change();
        });

        Schema::table('commercial_offer_requirements', function (Blueprint $table) {
            $table->string('requirement')->change();
        });
    }
};
