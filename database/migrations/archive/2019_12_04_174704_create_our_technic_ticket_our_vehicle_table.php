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
        Schema::create('our_technic_ticket_our_vehicle', function (Blueprint $table) {
            $table->unsignedInteger('our_technic_ticket_id');
            $table->unsignedInteger('our_vehicle_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('our_technic_ticket_our_vehicle');
    }
};
