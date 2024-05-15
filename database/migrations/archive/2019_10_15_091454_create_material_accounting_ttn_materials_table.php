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
        Schema::create('material_accounting_ttn_materials', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ttn_id');
            $table->string('count');
            $table->string('unit');
            $table->unsignedInteger('material_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_accounting_ttn_materials');
    }
};
