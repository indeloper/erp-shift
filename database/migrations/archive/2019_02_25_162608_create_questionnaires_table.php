<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->string('contact_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('pit_perimeter')->nullable();
            $table->string('pit_depth')->nullable();
            $table->string('pit_square')->nullable();

            $table->boolean('is_tongue')->default(0);
            $table->boolean('is_pile')->default(0);
            $table->boolean('is_soil_leader')->default(0);

            $table->string('binding_type')->nullable();
            $table->string('binding_count')->nullable();
            $table->string('binding_length')->nullable();

            $table->string('strut_type')->nullable();
            $table->string('strut_count')->nullable();
            $table->string('strut_diameter')->nullable();

            $table->string('racks_type')->nullable();
            $table->string('racks_count')->nullable();
            $table->string('racks_diameter')->nullable();

            $table->string('thrust_type')->nullable();
            $table->string('thrust_count')->nullable();
            $table->string('thrust_diameter')->nullable();

            $table->string('tables_count')->nullable();
            $table->string('gk_list_count')->nullable();
            $table->string('embedded_parts_count')->nullable();

            $table->string('soil_count')->nullable();
            $table->string('leader_count')->nullable();
            $table->string('leader_trench')->nullable();

            $table->text('comment')->nullable();
            $table->string('token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};
