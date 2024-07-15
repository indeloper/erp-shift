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
        Schema::create('commercial_offer_material_splits', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('wv_mat_id');
            $table->unsignedInteger('type');
            $table->float('count', 10, 3)->nullable();
            $table->unsignedInteger('time')->nullable();
            $table->float('security_price_one', 15, 2)->nullable();
            $table->float('security_price_result', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercial_offer_material_splits');
    }
};
