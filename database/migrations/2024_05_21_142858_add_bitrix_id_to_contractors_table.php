<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->unsignedBigInteger(
                'bitrix_id'
            )
                ->nullable()
                ->unique()
                ->after('id');

            $table->unique(['inn', 'kpp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropUnique('contractors_bitrix_id_unique');
            $table->dropUnique('contractors_inn_kpp_unique');
            $table->dropcolumn('bitrix_id');
        });
    }

};
