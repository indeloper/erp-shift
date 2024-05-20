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
        Schema::create('material_accounting_operation_materials', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('operation_id');
            $table->unsignedInteger('manual_material_id');

            $table->string('count')->default(0);
            $table->unsignedInteger('unit');

            $table->unsignedInteger('type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_accounting_operation_materials');
    }
};
