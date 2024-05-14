<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOurTechnicTicketOurVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('our_technic_ticket_our_vehicle', function (Blueprint $table) {
            $table->unsignedInteger('our_technic_ticket_id');
            $table->unsignedInteger('our_vehicle_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('our_technic_ticket_our_vehicle');
    }
}
