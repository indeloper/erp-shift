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
        Schema::create('manual_reference_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('attr_id')->index();
            $table->bigInteger('manual_reference_id')->index();
            $table->string('value');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_reference_parameters');
    }
};
