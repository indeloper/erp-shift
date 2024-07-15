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
        Schema::table('vacations_histories', function (Blueprint $table) {
            $table->boolean('change_authority')->default(0)->after('is_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacations_histories', function (Blueprint $table) {
            $table->dropColumn('change_authority');
        });
    }
};
