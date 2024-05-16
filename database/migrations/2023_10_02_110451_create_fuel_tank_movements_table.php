<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fuel_tank_movements', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedInteger('author_id')->comment('ID автора');
            $table->foreign('author_id')->references('id')->on('users');

            $table->bigInteger('fuel_tank_id')->unsigned()->comment('ID топливной емкости');
            $table->foreign('fuel_tank_id')->references('id')->on('fuel_tanks');

            $table->unsignedInteger('object_id')->nullable()->comment('ID объекта');
            $table->foreign('object_id')->references('id')->on('project_objects');

            $table->float('fuel_level', 8, 3);

            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement("ALTER TABLE fuel_tank_movements COMMENT 'Движение топлива в емкостях'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_tank_movements');
    }
};
