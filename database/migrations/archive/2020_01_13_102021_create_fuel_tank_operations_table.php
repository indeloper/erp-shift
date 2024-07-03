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
        Schema::create('fuel_tank_operations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('fuel_tank_id');
            $table->bigInteger('author_id');
            $table->bigInteger('object_id');
            $table->bigInteger('our_technic_id')->nullable();
            $table->bigInteger('contractor_id')->nullable();
            $table->float('value', 8, 3);
            $table->integer('type');
            $table->text('description')->nullable();
            $table->timestamp('operation_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_tank_operations');
    }
};
