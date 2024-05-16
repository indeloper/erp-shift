<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeUniqueFiledsInOurTechnicTicketUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('our_technic_ticket_user', function (Blueprint $table) {
            $table->renameColumn('our_technic_ticket_id', 'tic_id');
            $table->primary(['tic_id', 'user_id', 'type']);
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
            $table->dropPrimary(['tic_id', 'user_id', 'type']);
            $table->renameColumn('tic_id', 'our_technic_ticket_id');
        });
    }
}
