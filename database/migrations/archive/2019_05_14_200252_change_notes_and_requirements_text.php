<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeNotesAndRequirementsText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
    public function down()
    {
        Schema::table('commercial_offer_notes', function (Blueprint $table) {
            $table->string('note')->change();
        });

        Schema::table('commercial_offer_requirements', function (Blueprint $table) {
            $table->string('requirement')->change();
        });
    }
}
