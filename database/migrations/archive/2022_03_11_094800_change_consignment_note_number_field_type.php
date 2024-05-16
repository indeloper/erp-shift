<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('q3w_material_operations', function (Blueprint $table) {
            $table->string('consignment_note_number')->default('0')->comment('Номер ТТН')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE q3w_material_operations CHANGE consignment_note_number consignment_note_number INT UNSIGNED DEFAULT 0 NOT NULL COMMENT 'Номер ТТН'");
    }
};
