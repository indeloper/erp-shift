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
        Schema::create('q3w_material_operation_reasons', function (Blueprint $table) {
            $table->comment('Причины перемещения материалов');

            $table->id();
            $table->unsignedInteger('operation_route_id');
            $table->string('name')->comment('Причина движения материалов');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('operation_route_id')->references('id')->on('q3w_operation_routes')->onDelete('cascade');
        });

        Schema::table('q3w_material_operations', function (Blueprint $table) {
            $table->integer('material_operation_reason_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('q3w_material_operation_reasons');

        Schema::table('q3w_material_operations', function (Blueprint $table) {
            $table->dropColumn('material_operation_reason_id');
        });
    }
};
