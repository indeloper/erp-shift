<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeactivetedAtColumnToOurTechnicTicketUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('our_technic_ticket_user', function (Blueprint $table) {
            $table->string('deactivated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('our_technic_ticket_user', function (Blueprint $table) {
            $table->dropColumn('deactivated_at');
        });
    }
}